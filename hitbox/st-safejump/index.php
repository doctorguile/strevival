<?php
define('WP_USE_THEMES', true);
$wp_did_header = true;
require_once( '../../wp-load.php' );
wp();
if ( defined('WP_USE_THEMES') && WP_USE_THEMES ) do_action('template_redirect');

//$template = get_home_template();
///home/kuropp5/public_html/www.strevival.com/wp-content/themes/Sensei/index.php

get_header();
include '../../wp-content/themes/Sensei/header-bottom.php'; 
    
echo '<div id="content">';

require('safejump.php');

echo '</div>';
//get_sidebar();
get_footer();
