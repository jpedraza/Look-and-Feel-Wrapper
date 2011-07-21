<?php

//call the wrapper and initialize the class
include( 'class.fcc-look-and-feel.php' );
$wrapper = new FCC_Look_and_Feel();

//set the title of the final page
$wrapper->title = 'Test of the FCC Common Look and Feel';

//set our breadcrumb trail
$wrapper->breadcrumb = '<a href="#">Home</a> / <a href=#">Tests</a> / <a href="#">Look and Feel Test</a>';

//set slug
$wrapper->slug = 'index';

//set the URL
$wrapper->url = 'http://www.fcc.gov/search/';

//set the content div and settings
$wrapper->content_div = 'contentcontainer';
$wrapper->content_prepend = '<div id="maincontent" class="group"><div class="content-container">';
$wrapper->content_append = '</div></div>';

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
	<li>RESTful API can return either HTML or JSON</li>
	<li>Works even if the website is down</li>
</ul>

<h2>Examples</h2>
<ul>
	<li><a href="form.php">Enter any content</a> and see it in the look and feel</li>
	<li><a href="makeover.php">Enter a URL</a> to see the page transformed</li>
</ul>

<?php

//output the footer
echo $wrapper->get_footer();

?>