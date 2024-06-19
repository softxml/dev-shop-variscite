<?php
/**
 * The template for displaying the header.
 *
 * @package GeneratePress
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name = "format-detection" content = "telephone=no">
    <link rel="profile" href="http://gmpg.org/xfn/11">
    <?php wp_head(); ?>

    <!--=== CUSTOM LOAD TIME CALCULATION ===-->
    <script type="text/javascript">
        var timerStart = Date.now();
    </script>
</head>

<body <?php generate_body_schema();?> <?php body_class(); ?>>
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-NZ6BXV"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<!--=== CUSTOM LOAD TIME CALCULATION ===-->
<script type="text/javascript">
    $(document).ready(function() {
        console.log("Time until DOMready (GTM fires): ms", Date.now()-timerStart);
    });
    $(window).load(function() {
        console.log("Time until everything on page loaded: ms", Date.now()-timerStart);
    });
</script>

<div class="overlay"></div>
<?php
/**
 * generate_before_header hook.
 *
 * @since 0.1
 *
 * @hooked generate_do_skip_to_content_link - 2
 * @hooked generate_top_bar - 5
 * @hooked generate_add_navigation_before_header - 5
 */
do_action( 'generate_before_header' );

/**
 * generate_header hook.
 *
 * @since 1.3.42
 *
 * @hooked generate_construct_header - 10
 */
do_action( 'generate_header' );

/**
 * generate_after_header hook.
 *
 * @since 0.1
 *
 * @hooked generate_featured_page_header - 10
 */
do_action( 'generate_after_header' );
?>

<div id="page" class="hfeed site grid-container container grid-parent">
    <div id="content" class="site-content">
<?php
/**
 * generate_inside_container hook.
 *
 * @since 0.1
 */
do_action( 'generate_inside_container' );
