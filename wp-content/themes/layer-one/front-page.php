<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Home page: the page content is made of blocks (hero spotlight first).
 */
get_header();

while ( have_posts() ) {
	the_post();
	the_content();
}

get_footer();
