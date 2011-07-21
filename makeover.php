<?php
include( 'class.fcc-look-and-feel.php' );
$w = new FCC_Look_and_Feel();

//set slug
$w->slug = 'makeover';

//set the URL
$w->url = 'http://www.fcc.gov/search/';

//set the content div and settings
$w->content_div = 'contentcontainer';
$w->content_prepend = '<div id="maincontent" class="group"><div class="content-container">';
$w->content_append = '</div></div>';

$w->title = 'FCC Makeover';
$w->add_style( 'style.css');

if ( !isset( $_GET['page'] ) ) {
	echo $w->get_header();
	?>
	<form method="get">
		<div class="form-row">
			<div class="form-label">
				<label for="page">Page to makeover:</label>
			</div>
			<div class="form-field">
				<input type="text" id="paeg" name="page" value="http://" size="50" />
			</div>
		</div>
		<div class="form-row">
			<div class="form-label">
				<label for="page">&nbsp;</label>
			</div>
			<div class="form-field">
				<input type="submit" value="Makeover" />
			</div>
		</div>
	</form>
	<?php
	echo $w->get_footer();
	exit();
}

//get the page	
$data = file_get_contents( $_GET['page'] );

//check that we got data
if ( !$data ) 
	die('could not retrieve page');

//set title
preg_match('#\<title>(.*)\</title>#ism', $data, $title );
$w->title = $title[1];

//strip header
$data = substr( $data, stripos( $data, '<body' ) );

$w->slug = $_GET['page'];

echo $w->get_header();

//make relative links absolute and output
echo preg_replace( '#(href|src)=\"(/[^\"]+)\"#ism', '$1="' . $_GET['page'] . '$2"', $data );	

echo $w->get_footer();

	
?>