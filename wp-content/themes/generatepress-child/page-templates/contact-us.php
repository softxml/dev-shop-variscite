<?php
/**
 * Template Name: Contact Us
 */
get_header();
?>

    <div id="primary" <?php generate_content_class();?>>
        <main id="main" <?php generate_main_class(); ?>>
            <?php
                do_action('generate_before_main_content');
                while(have_posts()): the_post();
            ?>

            <section class="page-contact-us__top">
                <div class="container">
                    <?php
                        if(function_exists('yoast_breadcrumb')) {
                            yoast_breadcrumb('<p id="breadcrumbs">', '</p>');
                        }
                    ?>

                    <h1><?php the_title(); ?></h1>
                </div>
            </section>

            <style>
                .page-contact-us__bottom:after {
                    background: url(<?php echo get_field('variscite__page-contact--bg')['url']; ?>) center center no-repeat;
                    background-size: cover;
                }

                @media only screen and (max-width: 990px) {

                    .page-contact-us__bottom--left {
                        background: url(<?php echo get_field('variscite__page-contact--bg')['url']; ?>) center center no-repeat;
                        background-size: cover;
                    }
                }
            </style>

            <section class="page-contact-us__bottom">
                <div class="container">
                    <div class="page-contact-us__bottom--left">
                        <div class="page-contact-us__bottom--left-top">
                            <?php echo wpautop(get_field('variscite__page-contact--main')); ?>
                        </div>

                        <ul class="page-contact-us__bottom--left-locations">
                            <?php
                                $locations = get_field('variscite__page-contact--locations');
                                foreach($locations as $location):
                            ?>

                            <li class="page-contact-us__bottom--left-location">
                                <strong><?php echo $location['the_name']; ?></strong>
                                <?php echo wpautop($location['the_address']); ?>
                            </li>

                            <?php endforeach; ?>
                        </ul>

                        <div class="page-contact-us__bottom--left-icons">
                            <?php
                            $icons = get_field('variscite__page-contact--icons');
                            foreach($icons as $icon):
                                ?>

                                <a href="<?php echo $icon['the_url']; ?>" target="_blank" class="page-contact-us__bottom--left-icon">
                                    <i class="fa <?php echo $icon['the_icon_class']; ?>"></i>
                                </a>

                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="page-contact-us__bottom--right">
                        <?php echo do_shortcode(get_field('variscite__page-contact--form')); ?>
                    </div>
                </div>
            </section>

            <?php
                endwhile;
                do_action( 'generate_after_main_content' );
            ?>
        </main>
    </div>

<?php
do_action('generate_after_primary_content_area');
get_footer();
