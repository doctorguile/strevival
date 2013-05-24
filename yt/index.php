<?php
define('WP_USE_THEMES', true);
$wp_did_header = true;
require_once( '../wp-load.php' );
wp();
if ( defined('WP_USE_THEMES') && WP_USE_THEMES ) do_action('template_redirect');

get_header();
include '../wp-content/themes/Sensei/header-bottom.php';

echo '<div id="content">';

require('yt.php');

echo '</div>';
//get_sidebar();
get_footer();
