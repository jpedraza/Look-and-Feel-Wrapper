FCC Look and Feel Wrapper
=========================

Proof-of-concept prototype to provide a simple PHP wrapper to port the new site's look and feel to legacy pages and systems.

Features
--------

* Simple PHP wrapper to port one site's look and feel to another's content (rendering content independent from styling)
* Allows you inject any page or HTML into another site's look and feel
* Works with any site; simply provide the ID of the content container
* Caches each request, either to disk or using APC
* Allows you to add custom stylesheets
* Allows you to add custom javascript, either to the header or footer
* RESTful API can return either HTML or JSON
* Works even if the website is down

How to Use the Wrapper
----------------------

1. Initialize the class

	//call the wrapper and initialize the class
	include( 'class.fcc-look-and-feel.php' );
	$wrapper = new FCC_Look_and_Feel();

2. Set the source page

	//set the URL
	$wrapper->url = 'http://www.fcc.gov/search/';

3. Describe the source page's layout

	//set the content div and settings
	$wrapper->content_div = 'contentcontainer';
	$wrapper->content_prepend = '<div id="maincontent" class="group"><div class="content-container">';
	$wrapper->content_append = '</div></div>';
	
4. Set the title of the resulting page

	//set the title of the final page
	$wrapper->title = 'Test of the FCC Common Look and Feel';

5. Output the header

	echo $wrapper->get_header();

6. Output your content

	echo "Lorem Ipsum";
	
7. Output the footer

	echo $wrapper->get_footer();
	

Parameters
----------

* url -- URL Of source page (the "look-and-feel" page)
* domain -- Domain to generate absolute URLS, will be generated off of $url if none is given
* title -- Title to use for <title> and <h1> tag
* scripts -- array of scripts to inject in header or footer, format array( 'src' = 'http://...', 'header' => true) (whether to put in header instead of footer). Can also use add_script() to set.
* styles -- array of stylesheets to inject into the header, format array( 'http://..', 'http://...' ). Can also use add_style() to set.
* ttl -- TTL in Seconds to cache pages; 3600 = 1 hour, 1800 = 30 min, 900 = 15 (default is 3600)
* slug -- unique page identifier; helps with caching if 2 or more pages on same server
* use_apc -- whether or not to use APC for caching (defaults to disk caching, though if APC is available, would increase performance)
* content_div -- id of the div on the source page containing the page's content (will be removed)
* content_prepend -- HTML to prepend to the content div before returning the header (optional)
* content_append -- HTML to append to content div before returning the footer (optional)
	
Methods
-------

* get_header() -- returns the current page's header
* get_footer() -- returns the current page's footer
* clear_cache() -- clears the cache for the current page
* add_style( $url ) -- adds a stylesheet to the header given a given sylesheet's URL
* add_script( $url, $header = false ) -- adds a javascript file given the source URL. Defaults to the footer.

How to use the API
------------------

To use the API, simply submit requests directly to api.php. Any parameter in the class can be passed via either GET or POST.

The API also accepts the following additional parameters:

* clear-cache -- (bool) purges the cache prior to processing the request
* format -- format to return the data, either json or HTML, defaults to HTML
* part -- what to return, either header or footer. Defaults to both
* content -- for HTML requests with the "part" parameter, the content to inject in between the header and footer
	
Included Examples
-----------------

* index.php -- Static HTML content in the new FCC.gov's look and feel
* form.php -- Form to inject any HTML content into the new site's look and feel
* makeover.php -- Enter a URL to see that site's content in the new look and feel

Caching
-------

To ensure the wrapper does not compromise performance or uptime, caching occurs on two levels:

1. Each request to the souce page is cached in its entirety. This saves time and bandwidth by limiting remote calls. TTL will be ignored and the cache will be used if the source site becomes inaccessable (allowing the wrapper to continue to work).
2. Each header and footer generated is cached to a static file (or APC). The header and footer is automatically regenerated after a set period of time, or if clear_cache() is called.

