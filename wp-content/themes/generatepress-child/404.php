<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 * @package GeneratePress
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

get_header(); ?>

    <div id="primary" <?php generate_content_class(); ?>>
        <main id="main" <?php generate_main_class(); ?>>
            <?php
            /**
             * generate_before_main_content hook.
             *
             * @since 0.1
             */
            do_action( 'generate_before_main_content' );
            ?>

            <div class="inside-article">
                <?php
                /**
                 * generate_before_content hook.
                 *
                 * @since 0.1
                 *
                 * @hooked generate_featured_page_header_inside_single - 10
                 */
                do_action( 'generate_before_content' );
                ?>

                <div class="row">
                    <div class="col-md-6 page_info">

                        <header class="entry-header">
                            <h1 class="entry-title" itemprop="headline"><?php echo apply_filters( 'generate_404_title', __( 'You weren\'t suppose to see this…', 'generatepress' ) ); // WPCS: XSS OK. ?></h1>
                        </header><!-- .entry-header -->

                        <?php
                        /**
                         * generate_after_entry_header hook.
                         *
                         * @since 0.1
                         *
                         * @hooked generate_post_image - 10
                         */
                        do_action( 'generate_after_entry_header' );
                        ?>

                        <div class="entry-content" itemprop="text">
                            <?php
                            echo '<p>' . apply_filters( 'generate_404_text', __( 'We are working on the next thing', 'generatepress' ) ) . '</p>'; // WPCS: XSS OK.

                            //                    get_search_form();
                            ?>
                        </div><!-- .entry-content -->

                        <div class="return_to_home">
                            <div>In the meantime return or browse our products:</div>
                            <a href="/" class="home_link">Home</a>
                        </div>

                    </div>

                    <div class="col-md-6  page_info">
                        <img src="/wp-content/uploads/2018/11/variscite-404-green-chip.jpg" alt="Green Chip" title="" class="img-responsive">
                    </div>
                </div>

                <?php
                /**
                 * generate_after_content hook.
                 *
                 * @since 0.1
                 */
                do_action( 'generate_after_content' );
                ?>

            </div><!-- .inside-article -->

            <?php
            /**
             * generate_after_main_content hook.
             *
             * @since 0.1
             */
            do_action( 'generate_after_main_content' );
            ?>
        </main><!-- #main -->
        <div class="error404_products_wrap">
            <div class="cat_container">

                <?php
                if(is_active_sidebar('404page-widget')){
                    dynamic_sidebar('404page-widget');
                }?>

            </div>
        </div>
    </div><!-- #primary -->

<?php
/**
 * generate_after_primary_content_area hook.
 *
 * @since 2.0
 */
do_action( 'generate_after_primary_content_area' );

generate_construct_sidebars();

get_footer();
