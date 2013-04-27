<?php
//define('WP_USE_THEMES', true);
//require('../wp-blog-header.php');
/*
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>Super Street Fighter II Turbo - Character Hitbox Diagrams</title>
<style>
body {
	background-color:black;	
	font-family: sans-serif;
}

a:hover {
	color:blue;	
	text-decoration:underline;
}

a {
	color:Gray;
	text-decoration:none;
}
</style>
<BODY>
</BODY>
</HTML>
*/
?>
<?php
define('WP_USE_THEMES', true);
$wp_did_header = true;
require_once( '../wp-load.php' );
wp();

if ( defined('WP_USE_THEMES') && WP_USE_THEMES )
	do_action('template_redirect');

get_header();
//get_sidebar();
include '../wp-content/themes/Sensei/header-bottom.php'; 
    
echo '<div id="content">';
echo "<h3>Character Hitbox Diagrams</h3>";
echo '<img src="images/charselect.png" width="393" height="237" border="0" usemap="#Map">';
require('ssf2st/util.php');
printImageMap('ssf2st/');
echo <<<EOF
<h3><a href="./ssf2st/compare.html">Compare Side by Side</a></h3>
<h3><a href="./ssf2st/theoryfighter.html">Drag N Drop Comparison</a></h3>
<h3><a href="./st-safejump/">Safe Jump Guide</a></h3>
EOF;
/*
echo <<<EOF
	<div id="primary" class="site-content">
		<div id="content" role="main">
			<table align=center cellpadding=10><tr><td>
			  <img src="./images/ssf2st.png" width="295" height="169" border="0">
			</td></tr>
			  <tr align=center><td><a href="./ssf2st">Character Hit Box Diagrams</a></td></tr>
			  <tr align=center><td><a href="./ssf2st/compare.html">Compare Side by Side</a></td></tr>
			  <tr align=center><td><a href="./ssf2st/theoryfighter.html">Drag N Drop Comparison</a></td></tr>
			  <tr align=center><td><a href="./st-safejump/">Safe Jump Guide</a></td></tr>
			  </table>
		</div>
    </div>
EOF;
*/
get_footer();
