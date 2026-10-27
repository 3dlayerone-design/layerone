<?php
/**
 * Fallback template. Page sections are added later.
 */
get_header();
?>
<div class="wrap" style="padding-block:48px">
<?php
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		the_title( '<h1>', '</h1>' );
		the_content();
	}
}
?>
</div>
<?php
get_footer();
