<?php

//call the wrapper and initialize the class
include( 'class.fcc-look-and-feel.php' );
$wrapper = new FCC_Look_and_Feel();

//set the title of the final page
$wrapper->title = 'Test of the FCC Common Look and Feel';

//turn the sidebar on
$wrapper->sidebar = true;

//set our breadcrumb trail
$wrapper->breadcrumb = '<a href="#">Home</a> / <a href=#">Tests</a> / <a href="#">Look and Feel Test</a>';

//slug
$wrapper->slug = 'example';

//output the header
echo $wrapper->get_header();

//output our content

?>

<h1>FCC Look and Feel Test</h1>

<ul>
	<li>Allows you inject any HTML into the new site's look and feel</li>
	<li>Caches each request, either to disk or using APC</li>
	<li>Allows you to add custom stylesheets</li>
	<li>Allows you to add custom javascript, either to the header or footer</li>
	<li>Add your own breadcrums</li>
	<li>Toggle the sidebar on and off</li>
	<li>Works even if the website is down</li>
</ul>

<?php

//output the footer
echo $wrapper->get_footer();

?>