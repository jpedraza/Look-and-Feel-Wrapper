<?php
/**
 * Provides restful API for FCC Look and Feel Wrapper
 * All parameters can be passed either via GET or via POST
 * Returns as HTML or JSON data
 * @author Benjamin J. Balter
 */
 
//get the wrapper and init
include( 'class.fcc-look-and-feel.php' );
$w = new FCC_Look_and_Feel();

//user settable fields, either post or get
$params = array( 'url', 'domain', 'title', 'scripts', 'styles', 'breadcrumb', 'ttl', 'slug', 'content_append', 'content_prepend', 'content_div');

//loop through each field and store
foreach( $params as $param ) 
	if ( isset( $_REQUEST[ $param ] ) ) $w->$param = stripslashes( $_REQUEST[ $param ] );


//if no slug is given, generate a random one to prevent cache collision
if ( $w->slug == '' )
	$w->slug = time();
	
//clear cache if asked
if ( isset( $_REQUEST[ 'clear_cache' ] ) )
	$w->clear_cache();
	
//serve parts as JSON if requested
if ( isset( $_REQUEST['format'] ) && $_REQUEST['format'] == 'json' ) {
	$output['header'] = $w->get_header();
	$output['footer'] = $w->get_footer();
	header('Content-type: application/json');
	echo json_encode( $output );
	exit();
}

//if a particular part is being requested
if ( isset( $_REQUEST['part'] ) ) {
	
	if ( $_REQUEST['part'] == 'header' ) 
		echo $w->get_header();
		
	if ( $_REQUEST['part'] == 'footer' ) 
		echo $w->get_footer();
	
	exit();
	
}

//Default: generate the full HTML file and serve
echo $w->get_header();
echo $_REQUEST[ 'content' ];
echo $w->get_footer();


?>