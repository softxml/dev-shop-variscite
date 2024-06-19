<?php

// TODO - add option to disable header and footer for themes where the preview does not work well
get_header(); ?>

<style>
    .dc_dcb_content-area{
        max-width: 1200px;
        margin: 10px auto 20px;
        display: table;
    }
</style>

	<div class="dc_dcb_wrap">
		<div class="dc_dcb_content-area">
			<main class="dc_dcb_site-main">

				<?php
				while ( have_posts() ) : the_post();

					if(function_exists('dc_dcb_dev_content_block')) echo do_shortcode('[dcb id='. get_the_ID() .']');
				endwhile; // End of the loop.
				?>

			</main><!-- #main -->
		</div><!-- #primary -->
	</div><!-- .wrap -->

<?php get_footer();