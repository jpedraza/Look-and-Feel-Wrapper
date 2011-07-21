<?php
/**
 * FCC Look and Feel Wrapper
 * 
 * Allows new site's look and feel to be ported to any appliation
 * @author Benjamin J. Balter
 * @version 1.0
 */
 
class FCC_Look_and_Feel {

	//internal settings, must be changed in the file itself
	private $version = '1.0'; //version
	private $cache_dir = 'cache/';
	
	//attributes for storage, cannot be directly referenced. Use getHeader() and getFooter() instead
	private $header = ''; //Formatted header
	private $footer = ''; //Formatted footer
	private $page = ''; //Raw source page data

	//setting that can be set by the user via the class
	public $url = ''; //URL Of source page to fetch
	public $domain = ''; //Domain to generate absolute URLS, will be generated off of $url if none is given
	public $title = ''; //Title to use for <title> and <h1> tag
	public $scripts = array(); //array of scripts to inject in header or footer, format array( 'src' = 'http://...', 'header' => true) (whether to put in header instead of footer)
	public $styles = array(); //array of stylesheets to inject into the header, format array( 'http://..', 'http://...' )
	public $breadcrumb = ''; //HTML breadcrumbs to inject in placeholder
	public $ttl = 3600; //TTL in Seconds; 3600 = 1 hour, 1800 = 30 min, 900 = 15
	public $slug = ''; //helps caching if 2 or more pages on same server
	public $use_apc = false; //whether or not to use APC for caching
	public $content_div = ''; //id of div containing content
	public $content_prepend = ''; //HTML to prepend to content
	public $content_append = ''; //HTML to append to content 
	
	/**
	 * Fetches the source page to grab the look and feel
	 */
	function fetch_page() {

		//check to see if we already have the page, if so, return cache
		if ( $this->page != '' )
			return $this->page;
			
		if ( ( $this->page = $this->get_cache( $this->url ) ) )
			return $this->page;

		//get page
		$this->page = file_get_contents( $this->url );
		
		//if page failed, try to get page from cache regardless of TTL
		if ( !$this->page )
			$this->page = $this->get_cache( $this->url, false );
			
		//cannot get page
		if ( !$this->page )
			return false;

		//remove content from content div
		$this->page = $this->remove_content( $this->page );

		//cache without TTL in case site goes down
		$this->set_cache( $this->url, $this->page, false );
		$this->page .= $this->signature();

		//generate elements
		$this->header = $this->get_header();
		$this->footer = $this->get_footer();
		
		return $this->page;
	
	}
	
	/**
	 * Replaces the content div's content with an MD5 of the page's URL
	 * @param string $content the raw HTML
	 * @returns string the html without the content
	 */
	 function remove_content( $html ) {

		$dom = new DOMDocument;
		@$dom->loadHTML( $html );
		$dom->getElementById( $this->content_div )->nodeValue = md5( $this->url );
		return $dom->saveHTML();
		
	}
	
	/**
	 * Store data either in a disk cache or via APC
	 * @param string $slug unique slug to identify cache
	 * @param string $data the data to store
	 * @param bool $use_ttl for APC only, whether to set a TTL
	 * @return bool success or failure
	 */
	function set_cache( $slug, $data, $use_ttl = true ) {
	
		if ( $this->use_apc ) {
			$ttl = ( $use_ttl ) ? $this->ttl : null;
			return apc_store( md5( $slug ), $data, $ttl );
 		}
		
		return file_put_contents( $this->cache_dir . md5( $slug ) . '.html', $data . $this->signature() );
				
	}
	
	/**
	 * Retrives cached data
	 * @param string $slug unique slug to identify cache
	 * @param bool $use_ttl for disk cache only, whether to honor the TTL
	 * @retruns string the data
	 */
	function get_cache( $slug, $use_ttl = true ) {
	
		//check APC store first
		if ( $this->use_apc )
			return $this->remove_signature( apc_fetch( md5( 'slug' ) ) );
		
		//verify file exists
		if ( !is_file( $this->cache_dir . md5( $slug ) . '.html' ) )
			return false;
		
		//retrieve disk cache
		$file = file_get_contents( $this->cache_dir . md5( $slug ). '.html' );

		//check ttl
		$timestamp = strtotime( substr( $file, -23, 19 ) );
		
		//if age is > TTL, kill and kick
		if ( $use_ttl && ( time() - $timestamp ) > $this->ttl ) {
			@unlink( $this->cache_dir . md5( $slug ). '.html' );
			return false;
		}
		
		return $this->remove_signature( $file );
		
	}
	
	/**
	 * Clears all cache for the current page and slug
	 */
	function clear_cache( ) {
	
		$slugs = array( $this->url, $this->slug . '-header', $this->slug . '-footer' );
		foreach ( $slugs as $slug )
			$this->remove_cache( $slug );
		
	}
	
	/**
	 * Removes a specific obeject from the cache
	 * @param string $slug unique cache identifier
	 * @returns bool sucess or fail
	 */
	function remove_cache( $slug ) {
		
		//remove from APC
		if ( $this->use_apc ) 
			return apc_delete( $slug );

		//remove disk cache
		return @unlink( $this->cache_dir . md5( $slug ). '.html' );
			
	}
	
	/** 
	 * Helper function to get and formatted header
	 */
	function get_header() {
	
		//if we already have header, return
		if ( $this->header != '' )
			return $this->header;
				
		//check cache
		if ( ( $this->header = $this->get_cache( $this->slug . '-header' ) ) )
			return $this->header;
		
		//get the raw page data
		$page = $this->fetch_page();
		
		//trim at our start needle and store
		$this->header = substr( $page, 0, strpos( $page, md5( $this->url ) ) );
		
		//inject title
		$this->header = $this->inject_titles( $this->header );
	
		//inject breadcrumbs
		$this->header = $this->inject_breadcrumbs( $this->header );
		
		//make relative URLs absolute
		$this->header = $this->relative_to_absolute( $this->header );
		
		//add our styles
		$this->header = $this->inject_styles( $this->header );
		
		//add our scripts
		$this->header = $this->inject_scripts( $this->header, 'header');
		
		//prepend content
		$this->header .= $this->content_prepend;
		
		//cache
		$this->set_cache( $this->slug . '-header', $this->header );
		
		return $this->header;
	
	}
	
	/**
	 * Injects title in to <title> and <h1> tags
	 */
	function inject_titles( $html ) {
		
		//inject into <title> tag
		$html = preg_replace( '#<title[^>]*>[^<]+</title>#ism', '<title>' . $this->title . '</title>', $html );
		
		//inject into <h1> tag
		$html = preg_replace( '#<h1[^>]*>[^<]+</h1>#ism', '<h1>' . $this->title . '</h1>', $html );
		
		return $html;
	}

	/**
	 * Injects breadcrums or removes breadcrumb section
	 */
	function inject_breadcrumbs( $html ) {

		//set the replace string
		$breadcrumb = ( $this->breadcrumb == '' ) ? '' : '<div class="breadcrumb">' . $this->breadcrumb . '</div>';

		//replace
		$html = preg_replace( '#\<div class=\"breadcrumb\">(.*?)\</div>#i', $breadcrumb, $html );
	
		return $html;
	
	}
	
	/**
	 * Helper function to get and format the footer
	 */
	function get_footer() {
	
		//if we already have footer, return
		if ( $this->footer != '' )
			return $this->footer;
				
		//get footer from cache
		if ( ( $this->footer = $this->get_cache( $this->slug . '-footer' ) ) )
			return $this->footer;

		//get the raw page data
		$page = $this->fetch_page();
		
		//trim at our start needle and store
		$this->footer .= substr( $page, strpos( $page, md5( $this->url ) ) + 32 );
		
		//make relative URLs absolute
		$this->footer = $this->relative_to_absolute( $this->footer );
		
		//add our scripts
		$this->footer = $this->inject_scripts( $this->footer, 'footer');
		
		//append content
		$this->footer = $this->content_append . $this->footer;
		
		//cache
		$this->set_cache( $this->slug . '-footer', $this->footer );
		
		return $this->footer;
	
	}
	
	/**
	 * Helper function to get domain, or generate if necessary
	 */
	function get_domain() {
	
		//return the specified domain if set
		if ( $this->domain != '' )
			return $this->domain;
			
		//expode domain into parts by /s
		$domain = explode( '/', $this->url );
		
		//combine the first three parts as the domain
		$this->domain = implode('/', array( $domain[0],  $domain [1] ,  $domain[2] ) );
		
		return $this->domain;
		
	}
	
	/**
	 * Makes relative stylesheets, images, and javascript files absolute
	 */
	function relative_to_absolute( $html = '' ) {
		return preg_replace( '#(href|src)=\"(/[^\"]+)\"#ism', '$1="' . $this->get_domain() . '$2"', $html );	
	}

	/**
	 * Allows users to add scripts to script array
	 * @param string $src URL to script
	 * @param bool $header whether to include in the header
	 */
	function add_script( $src, $header = false ) {

		$this->scripts[] = array( 'src' => $src, 'header' => $header);
			
	}	
	
	/**
	 * Allows users to add stylesheets to the styles array
	 * @param string $src the URL to the stylesheet
	 */
	function add_style ( $src ) {
		
		$this->styles[] = $src;
		
	}

	/**
	 * Parses scripts array into string for given part of page
	 * @param string $part either header or footer\
	 * @returns string script include HTML
	 */
	function get_scripts( $part ) {
	
		$output = array();
		
		//verify that there are scripts
		if ( sizeof( $this->scripts ) == 0 )
			return '';
	
		//convert string $part into a bool
		$header = ( $part == 'header' );
		
		//loop through scripts and generate tags
		foreach ( $this->scripts as $script ) {
			
			//verify the current script is for the current part
			if ( $script['header'] == $header )
				$output[] = '<script type="text/javascript" src="' . $script['src'] . '"></script>';
		
		}
		
		//and closing tag
		$output[] = ( $part == 'header' ) ? '</head>' : '</body>';
		
		//implode array to string and retun
		return implode("\n", $output);
	
	}
	
	/**
	 * Parses stylesheets from array to HTML
	 * @returns string include HTML
	 */
	function get_styles() {
	
		$output = array();
	
		//verify that there are stylesheets
		if ( sizeof( $this->styles ) == 0 )
			return '';
		
		//loop through and format
		foreach ( $this->styles as $style ) 
			$output[] = '<link type="text/css" rel="stylesheet" media="all" href="' . $style . '" />';

		//add closing tag
		$output[] = "</head>";
	
		return implode("\n", $output );
		
	}
	
	/**
	 * Adds stylesheets to HTML prior to </head> tag
	 * @param string $html the Raw HTML of the page
	 * @return string the HTML with stylesheets
	 */
	function inject_styles( $html ) {
	
		//verify that there are stylesheets
		if ( sizeof( $this->styles ) == 0 )
			return $html;

			//grab the HTML
		$styles = $this->get_styles();
		
		//inject before closing tag
		return str_replace( '</head>', $styles, $html );
	
	}
	
	/**
	 * Injects script array before appropiate </tag>
	 * @param string $html raw html to filter
	 * @param string $part either header or footer
	 * @return string filtered HTML with scripts
	 */
	function inject_scripts( $html, $part ) {
		
		//verify we have scripts to inject
		if ( sizeof( $this->scripts ) == 0 )
			return $html;

		//is this header or footer, convert to bool
		$needle = ( $part == 'header' ) ? '</head>' : '</body>';

		//grab scripts for this part as a string
		$scripts = $this->get_scripts( $part );
		
		//inject before closing tag
		return str_replace( $needle, $scripts, $html );
				
	}
	
	/**
	 * Generates signature for cache file
	 * @returns string the signature
	 */
	function signature() {
	
		return '<!-- FCC Common Look and Feel Wrapper ' . $this->version . ' -- Generated by ' . $_SERVER['SERVER_NAME'] . ' at ' . date('Y-m-d H:i:s') . ' -->';
	
	}
	
	/**
	 * Strips class-generated signature from a HTML string
	 * @param string $html the HTML haystack
	 * @returns string html without signature
	 */
	function remove_signature( $html ) {
		return substr( $html, 0, strripos( $html, '<!--') );
	}
}

?>