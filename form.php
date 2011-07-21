<?php

include( 'class.fcc-look-and-feel.php' );
$w = new FCC_Look_and_Feel();

//set title
$w->title = "FCC Common Look and Feel";

//add style
$w->add_style( 'style.css');

//set slug
$w->slug = 'form';

//set the URL
$w->url = 'http://www.fcc.gov/search/';

//set the content div and settings
$w->content_div = 'contentcontainer';
$w->content_prepend = '<div id="maincontent" class="group"><div class="content-container">';
$w->content_append = '</div></div>';

echo $w->get_header();
?>
<h1>FCC Common Look and Feel</h1>
<form method="get" action="api.php">
<div class="form-row">
	<div class="form-label">
		<label for="title">Page Title:</label>
	</div>
	<div class="form-field">
		<input type="text" id="title" name="title" size="50" />
	</div>
</div>
<div class="form-row">
	<div class="form-label">
		<label for="content">Page Content:</label>
	</div>
	<div class="form-field">
		<textarea name="content"></textarea>
	</div>
</div>
<div class="form-row">
	<div class="form-label">
		<label for="title">&nbsp;</label>
	</div>
	<div class="form-field">
		<input type="submit" value="Generate Page" />
	</div>
</div>
<input type="hidden" name="url" value="http://www.fcc.gov/search/" />
<input type="hidden" name="content_div" value="contentcontainer" />
<input type="hidden" name="content_prepend" value='<div id="maincontent" class="group"><div class="content-container">' />
<input type="hidden" name="content_append" value="</div></div>" />
</form>
<?php echo $w->get_footer(); ?>