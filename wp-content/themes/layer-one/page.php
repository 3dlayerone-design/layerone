<?php
/**
 * Pages. A page built from Layer One blocks is shown full width without a title; any other page gets a plain title and text.
 */
get_header();

while ( have_posts() ) {
	the_post();
	if ( false !== strpos( get_the_content(), 'wp:layerone/' ) || ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() || is_account_page() || is_page( array( 'wishlist', 'track-order' ) ) ) ) ) {
		the_content();
		continue;
	}
	?>
	<div class="wrap" style="padding-block:48px">
		<?php the_title( '<h1>', '</h1>' ); ?>
		<?php the_content(); ?>
	</div>
	<?php
}

get_footer();
