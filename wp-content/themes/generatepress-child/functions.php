<?php
// (@AsafA) FILE VERSION 
function theme_get_file_ver($file)
{
    return filemtime(get_stylesheet_directory() . $file);
}
function product_som_special_offer_quantity($product_id)
{
    $product_som_special_offer_quantity = get_field('product_som_special_offer_quantity', $product_id);
    return $product_som_special_offer_quantity ? $product_som_special_offer_quantity : 1000;
}

function get_product_som_special_offer_max($variation_id, $product_id)
{
    if (!$variation_id && !$product_id) {
        return false;
    }
    $product_max = false;
    $is_product_display_som_special_offer = has_term('system-on-module', 'product_cat', $product_id) && get_field('product_display_som_special_offer', $product_id) ? true : false;
    if ($is_product_display_som_special_offer) {
        $product = wc_get_product($variation_id);
        $prod_variations = $product->get_variation_attributes();
        // loop through array and add to field 'choices'  
        if (is_array($prod_variations)) {
            foreach ($prod_variations as $taxonomy => $terms) {
                $term = get_term_by('slug',  $terms, str_replace('attribute_', '', $taxonomy));
                if (get_field('product_display_som_special_offer_product_variation', $product_id) == $term->term_id) {
                    $product_max = product_som_special_offer_quantity($product_id);
                    // $product_min = $product_max;
                }
            }
        }
        // if (get_field('product_display_som_special_offer_product_variation_item', $product_id)){}      
    }

    return $product_max;
}
function dc_child_theme_enqueue_scripts()
{

    $the_theme = wp_get_theme();

    wp_register_style('bootstrap', get_stylesheet_directory_uri() . '/bootstrap/css/bootstrap.min.css', null, $the_theme->get('Version'));
    wp_register_style('bootstrap-theme', get_stylesheet_directory_uri() . '/bootstrap/css/bootstrap-theme.min.css', null, $the_theme->get('Version'));
    wp_register_script('bootstrap', get_stylesheet_directory_uri() . '/bootstrap/js/bootstrap.min.js', null, $the_theme->get('Version'), true);
    wp_enqueue_style('bootstrap');
    wp_enqueue_style('bootstrap-theme');
    wp_enqueue_script('bootstrap');
    wp_enqueue_style('font-awesome-var', '//use.fontawesome.com/releases/v6.4.2/css/all.css', array(), 'v6.4.2');

    //	if(is_rtl()){
    //		wp_register_style( 'rtl', get_template_directory_uri() . '/rtl.css' );
    //		wp_enqueue_style( 'rtl' );
    //	}

    wp_deregister_style('generate-child');
    // (@AsafA)
    wp_register_style('child-style', get_stylesheet_directory_uri() . '/style.css', null, theme_get_file_ver('/style.css'));
    wp_enqueue_style('child-style');

    wp_register_style('slick-css', get_stylesheet_directory_uri() . '/slick/slick.css', null, $the_theme->get('Version'));
    wp_enqueue_style('slick-css');

    wp_register_style('slick-theme-css', get_stylesheet_directory_uri() . '/slick/slick-theme.css', null, $the_theme->get('Version'));
    wp_enqueue_style('slick-theme-css');

    if (get_page_template_slug() == 'page-templates/contact-us.php') {
        wp_register_style('variscite-contact-css', get_stylesheet_directory_uri() . '/custom/css/page-contact.css', null, $the_theme->get('Version'));
        wp_enqueue_style('variscite-contact-css');
    }

    //	if(is_rtl()){
    //		wp_register_style( 'child-rtl', get_stylesheet_directory_uri() . '/rtl.css' );
    //		wp_enqueue_style( 'child-rtl' );
    //	}

    // Enqueue Sticky Sidebar js library
    wp_register_script('sticky-sidebar', get_stylesheet_directory_uri() . '/js/stickykit.jquery.js', array('jquery'), $the_theme->get('Version'), true);
    wp_enqueue_script('sticky-sidebar');

    wp_register_script('form-validation', get_stylesheet_directory_uri() . '/js/validation.js', array('jquery'), $the_theme->get('Version'), true);
    wp_enqueue_script('form-validation');

    // (@AsafA)
    wp_register_script('child-scripts', get_stylesheet_directory_uri() . '/js/scripts.js', array('jquery'), theme_get_file_ver('/js/scripts.js'), true);
    wp_enqueue_script('child-scripts');

    //    if(is_front_page()) {
    //        wp_register_script('homepage-filtering', get_stylesheet_directory_uri() . '/js/homepage-filtering.js', array('jquery'), $the_theme->get('Version'), true);
    //        wp_enqueue_script('homepage-filtering');
    //    }

    wp_register_script('slick', get_stylesheet_directory_uri() . '/slick/slick.min.js', array('jquery'), $the_theme->get('Version'), true);
    wp_enqueue_script('slick');

    //check if front-end is being viewed
    if (!is_admin()) {
        // Remove default WordPress jQuery
        wp_deregister_script('jquery');

        // Register new jQuery script via Google Library
        wp_register_script('jquery', get_stylesheet_directory_uri() . '/js/jquery.min.js', false, '2.4.4');

        // Enqueue the script   
        wp_enqueue_script('jquery');
    }
}
add_action('wp_enqueue_scripts', 'dc_child_theme_enqueue_scripts', 600);

remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_print_styles', 'print_emoji_styles');

flush_rewrite_rules();
remove_action('template_redirect', 'wp_old_slug_redirect');

remove_filter('template_redirect', 'redirect_canonical');


function remove_loop_button()
{
    remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
}
add_action('init', 'remove_loop_button');


add_action('woocommerce_after_shop_loop_item', 'replace_add_to_cart');
function replace_add_to_cart()
{
    global $product;
    static $counter = 0;
    $counter++;
    $prod_data = $product->get_data();
    $prod_link = $product->get_permalink();
    $taxonomi = get_the_terms($product->get_id(),  'product_cat');
    $prod_id = $product->get_id();
    update_field('pro_index', $counter, $prod_id);
    $type = $taxonomi[0]->name;
    //    var_dump( $prod_data);
    $price = intval($prod_data['price']);
    //    echo '$product->ID c: '.$prod_id;
    echo '<a href="' . $prod_link . '" data-quantity="1" class="button add_to_cart_button" data-type="' . $type . '" data-price="' . $price . '" data-product_id="' . $prod_data['id'] . '" data-product_sku="' . $prod_data['sku'] . '" data-name="' . $prod_data['name'] . '"  aria-label="Select options for “' . $prod_data['name'] . '”">' . __('Order', 'woocommerce') . '</a>';
}


add_filter( 'wpseo_schema_graph', 'remove_brand_from_yoast_schema' );

function remove_brand_from_yoast_schema( $data ) {
    // Iterate through the schema graph array
    foreach ( $data as $index => $item ) {
        // Check if the item is a WebPage type
        
        if ( isset( $item['@type'] ) && $item['@type'] === 'WebPage' ) {
            // Check if the 'brand' key exists in the WebPage item and unset it
            if ( isset( $item['brand'] ) ) {
                // print_r($item);
                // exit(0);
                unset( $data[$index]['brand'] );
            }
        }
    }
    return $data;
}







function remove_woocommerce_breadcrumb_schema() {
    // Ensure WC_Structured_Data class is loaded
    if (class_exists('WC_Structured_Data')) {
        // Get the instance of the WC_Structured_Data class
        $instance = WC()->structured_data;

        // Ensure the instance and method exists
        if ($instance && method_exists($instance, 'generate_breadcrumblist_data')) {
            remove_action('woocommerce_breadcrumb', array($instance, 'generate_breadcrumblist_data'), 10);
        }
    }
}
add_action('wp_loaded', 'remove_woocommerce_breadcrumb_schema');



//Adding the Product loop Short Description
function tutsplus_excerpt_in_product_archives()
{
    echo '</div>';
    echo '<div class="product_short_description">';
    the_excerpt();
    echo '</div>';
}
add_action('woocommerce_after_shop_loop_item_title', 'tutsplus_excerpt_in_product_archives', 40);

//Product attribute shortcode
function get_woo_products_attributes()
{
    $attributes = get_terms(['taxonomy' => 'pa_cpu-name', 'hide_empty' => false]);
    //        ---
    $menu_name = 'products-attributes-menu';
    $locations = get_nav_menu_locations();
    $menu = wp_get_nav_menu_object($locations[$menu_name]);
    $menu_items = wp_get_nav_menu_items($menu->term_id, array());

    //        $chosen_attr = $_GET["filter_cpu-name"];

    $template = '<form id="filter_attributes">';
    foreach ($menu_items as $menu_item) {
        $template .= '<div class="checkbox-wrap">';
        $template .= '<input type="checkbox" id="' . $menu_item->post_name . '" value="' . $menu_item->post_excerpt . '" data-name="' . $menu_item->title . '">';
        $template .= '<label for="' . $menu_item->post_name . '"><span class="custom_checkbox"></span>';
        $template .= $menu_item->title;
        $template .=  '</label>';
        $template .=  '</div>';
    }
    $template .=  '</form>';

    //        $template = '<ul class="woocommerce-widget-layered-nav-list">';
    //            foreach( $menu_items as $menu_item ) {
    //                $template .=  '<li class="woocommerce-widget-layered-nav-list__item wc-layered-nav-term ">';
    //                    $template .=  '<a rel="nofollow" href="';
    //                        if ( is_product_category() ) {
    //                            $template .= '/product-category/'.get_queried_object()->slug;
    //                        }
    //                        $template .= '/?filter_cpu-name='. $menu_item->post_excerpt .'&amp;query_type_cpu-name=or">';
    //                        $template .=  $menu_item->title;
    //                    $template .=  '</a>';
    //                $template .=  '</li>';
    //            }
    //        $template .=  '</ul>';


    //        ---

    //        $template = '<form id="filter_attributes">';
    //            foreach ($attributes as $key => $value) {
    //                $template .= '<div class="checkbox-wrap">';
    //                    $template .= '<input type="checkbox" id="'. $value->slug.'" value="'. $value->slug.'" data-name="'.$value->name.'">';
    //                    $template .= '<label for="'. $value->slug.'"><span class="custom_checkbox"></span>';
    //                        $template .= $value->name;
    //                    $template .=  '</label>';
    //                $template .=  '</div>';
    //            }
    //        $template .=  '</form>';

    return  $template;
}
add_shortcode('woo-products-attributes', 'get_woo_products_attributes');

///////// include custom-checkout functions /////////
include "custom-checkout.php";


//Product categories shortcode
function get_woo_products_categories()
{
    $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true]);

    $template = '<form id="filter_categories">';
    foreach ($categories as $key => $value) {
        $template .= '<div class="checkbox-wrap">';
        $template .= '<input type="checkbox" id="' . $value->slug . '" value="' . $value->slug . '" data-name="' . $value->name . '">';
        $template .= '<label for="' . $value->slug . '"><span class="custom_checkbox"></span>';
        $template .= $value->name;
        $template .=  '</label>';
        $template .=  '</div>';
    }
    $template .=  '</form>';
    return $template;
}
add_shortcode('woo-products-categories', 'get_woo_products_categories');

// AJAX: construct top cart icon
function get_cart_count_and_dropdown()
{
    $cart = WC()->cart->get_cart_contents_count();

    $html = '<a href="/cart/" class="cart-contents" title="View your shopping cart">';

    if ($cart > 0) {
        $html .= '<span class="number-of-items">' . $cart . '</span></a>';
        $html .= '<ul class="cart_menu">';
        $html .= '<li><a id="cart_button" href="' . wc_get_cart_url() . '">' . __("Shopping Cart") . '</a></li><li><a id="check_button" href="' . wc_get_checkout_url() . '">' . __("Checkout") . '</a></li>';
        $html .= '</ul>';
    } else {
        $html .= '</a>';
    }

    die($html);
}
add_action('wp_ajax_get_cart_count_and_dropdown', 'get_cart_count_and_dropdown');
add_action('wp_ajax_nopriv_get_cart_count_and_dropdown', 'get_cart_count_and_dropdown');

//wc_cart_link
function variscite_wc_cart_link()
{
    ob_start();
?>
    <a href="<?php echo esc_url(wc_get_cart_url()); ?>" class="cart-contents" title="<?php esc_attr_e('View your shopping cart', 'generate-woocommerce'); ?>">
    </a>
<?php
    return ob_get_clean();
}

//wc_menu_cart
function variscite_wc_menu_cart($nav, $args)
{
    if ($args->theme_location == 'primary' && generatepress_wc_get_setting('cart_menu_item')) {

        if (WC()->cart != null) {
            $cart = WC()->cart->get_cart_contents_count();
        }

        $html = '<ul class="cart_menu">';

        if ($cart && $cart > 0) {
            $html .= '<li><a href="' . wc_get_cart_url() . '">' . __("Shopping Cart") . '</a></li><li><a href="' . wc_get_checkout_url() . '">' . __("Checkout") . '</a></li>';
        }

        $html .= '</ul>';

        return sprintf(
            '%1$s 
                <li class="wc-menu-item %4$s" title="%2$s">
                    %3$s' . $html . '</li>',
            $nav,
            esc_attr__('View your shopping cart', 'generate-woocommerce'),
            variscite_wc_cart_link(),
            is_cart() ? 'current-menu-item' : ''
        );
    }

    // Our primary menu isn't set, return the regular nav
    return $nav;
}

function custom_wpml_lang_switcher()
{
    if (function_exists('icl_get_languages')) {
        // remove WPML default css
        define('ICL_DONT_LOAD_LANGUAGE_SELECTOR_CSS', true);
        $languages = apply_filters('wpml_active_languages', null, 'orderby=id&order=desc');
        if (!empty($languages)) {
            $outerlist = '<ul><li>';
            $list = '';
            foreach ($languages as $language) {
                //$flag = $language['country_flag_url'];
                $url      = $language['url'];
                $isActive = $language['active'];
                $name = $language['native_name'];
                $code = $language['language_code'];

                if ($isActive == 1) {
                    $outerlist .=  $code . '<ul class="language-dropup">';
                }

                $list .= '<li><a';
                if ($isActive == 1) {
                    $list .= ' class="active"';
                }
                $list .= ' href="' . $url . '">' . $name . '</a></li>';
            }
            $list .= '</ul></li></li></ul>';
            return $outerlist . $list;
        }
    }
}

//wc_mobile_cart_link
function variscite_wc_mobile_cart_link()
{
    if (function_exists('generatepress_wc_get_setting') && !generatepress_wc_get_setting('cart_menu_item')) {
        return;
    }
?>
    <div class="mobile-bar-items wc-mobile-cart-items">
        <div class="language-switcher bottom-language-switcher">
            <?php echo custom_wpml_lang_switcher(); ?>
        </div>
        <?php do_action('generate_mobile_cart_items'); ?>
        <?php echo variscite_wc_cart_link(); ?>
    </div><!-- .mobile-bar-items -->
<?php
}

//woocommerce_add_to_cart_fragments
add_filter('woocommerce_add_to_cart_fragments', 'variscite_cart_link_fragment');
function variscite_cart_link_fragment($fragments)
{
    global $woocommerce;
    $fragments['.cart-contents span.number-of-items'] = (WC()->cart->get_cart_contents_count() > 0) ? '<span class="number-of-items">' . wp_kses_data(WC()->cart->get_cart_contents_count()) . '</span>' : '<span class="number-of-items">0</span>';
    return $fragments;
}

//variscite_wc_cart_item
add_action('after_setup_theme', 'variscite_wc_cart_item');
function variscite_wc_cart_item()
{
    remove_filter('wp_nav_menu_items', 'generatepress_wc_menu_cart', 10, 2);
    add_filter('wp_nav_menu_items', 'variscite_wc_menu_cart', 10, 2);

    remove_action('generate_inside_navigation', 'generatepress_wc_mobile_cart_link');
    remove_action('generate_inside_mobile_header', 'generatepress_wc_mobile_cart_link');

    add_action('generate_inside_navigation', 'variscite_wc_mobile_cart_link');
    add_action('generate_inside_mobile_header', 'variscite_wc_mobile_cart_link');
}


//option_generate_woocommerce_settings
//    add_filter( 'option_generate_woocommerce_settings','lh_custom_category_wc_columns' );
////    function lh_custom_category_wc_columns( $options ) {
////
////        $options['columns'] = 1;
////
////        return $options;
////    }

//Open WC image wrapper.
//    add_action( 'woocommerce_before_shop_loop_item_title' , 'variscite_wc_image_wrapper_open', 8 );
//    function variscite_wc_image_wrapper_open() {
////        echo '<div class="wc-product-image"><div class="inside-wc-product-image">';
//    }

//Close WC image wrapper.
add_action('woocommerce_shop_loop_item_title', 'variscite_wc_image_wrapper_close', 9);
function variscite_wc_image_wrapper_close()
{
    echo '<div class="wc-product-name-price">';
}

//WC add grid mode.
add_action('woocommerce_before_shop_loop', 'variscite_wc_grid_mode', 8);
function variscite_wc_grid_mode()
{
    echo '<div class="wc_grid_mode">
        <button class="btn btn-link active" id="rows" data-grid="rows"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="22" viewBox="0 0 24 22"> <g  fill-rule="evenodd"> <path d="M6 0h18v4H6zM6 12h18v4H6zM6 18h18v4H6zM6 6h18v4H6zM0 0h4v4H0zM0 12h4v4H0zM0 18h4v4H0zM0 6h4v4H0z"></path> </g> </svg> </button>
        <button class="btn btn-link" id="grid" data-grid="grid"><svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 22 22"> <g  fill-rule="evenodd"> <path d="M0 0h10v10H0zM12 0h10v10H12zM0 12h10v10H0zM12 12h10v10H12z"></path> </g> </svg> </button>
    </div>';
}

//Remove rating in shop loop
remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);


//WC add mobile filter image.
add_action('woocommerce_before_shop_loop', 'variscite_wc_mobile_filter_icon', 8);
function variscite_wc_mobile_filter_icon()
{
?>
    <div class="mobile_filter_wrap">
        <div class="mobile_filter_icon">
            <i class="fa fa-filter" aria-hidden="true"></i>
        </div>
        <div class="clear_filter">
            <a href="<?php echo get_home_url(); ?>"><?php _e('Clear', 'variscite'); ?></a>
        </div>
    </div>
<?php
}

//Remove pagination
remove_action('woocommerce_after_shop_loop', 'woocommerce_pagination', 10, 0);
add_action('woocommerce_after_shop_loop', 'custom_woocommerce_pagination');
function custom_woocommerce_pagination()
{

    global $wp_query;
    global $paged;
?>

    <div class="woocommerce-pagination">

        <?php
        if ($paged > 0) {
            previous_posts_link(__('Prev', 'variscite'), $wp_query->max_num_pages);
        }
        ?>

        <div><span><?php echo $paged == 0 ? 1 : $paged; ?></span> of <?php echo $wp_query->max_num_pages; ?></div>
        <?php

        if ($paged < $wp_query->max_num_pages) {
            next_posts_link(__('Next', 'variscite'), $wp_query->max_num_pages);
        }
        ?>
    </div>
    <?php
}

// Add prev and next classes to pagination
add_filter('next_posts_link_attributes', 'posts_next_link_attributes');
function posts_next_link_attributes()
{
    return 'class="next"';
}

add_filter('previous_posts_link_attributes', 'posts_prev_link_attributes');
function posts_prev_link_attributes()
{
    return 'class="prev"';
}

//// Update the paginated title
//function variscite_remove_page_number_from_title($title) {
//
//    if(is_front_page()) {
//        $home  = get_option('page_on_front');
//        $title = get_post_meta($home, '_yoast_wpseo_title', true);
//    }
//
//    return $title;
//}
//add_filter('wpseo_title', 'variscite_remove_page_number_from_title');

//Change the cart quantity look (@AsafA)
add_filter('variscite_woocommerce_cart_item_quantity', 'variscite_woocommerce_cart_item_quantity_callback', 10, 3);
function variscite_woocommerce_cart_item_quantity_callback($cart_item_key, $cart_item, $_product)
{
    if ($_product->is_sold_individually()) {
        $product_quantity = filter_actionsprintf('1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key);
    } else {
        // $max_value = $_product->backorders_allowed() ? '' : $_product->get_stock_quantity();
        $product_id = $_product->get_parent_id() ? $_product->get_parent_id() : $_product->get_id();

        $product_min = wc_get_product_min_limit($product_id);
        $product_max = wc_get_product_max_limit($product_id);

        if (isset($cart_item['variation_id'])) {
            $get_product_som_special_offer_max = get_product_som_special_offer_max($cart_item['variation_id'],  $product_id);
            if ($get_product_som_special_offer_max !== false) {
                $product_max = $get_product_som_special_offer_max;
                $product_min = $product_max;
            }
        }

        if (!empty($product_min)) {
            if (false !== $product_max) {
                $args['min_value'] = $product_min;
            }
        } else {
            $args['min_value'] = 0;
        }
        if (!empty($product_max)) {
            if (false !== $product_max) {
                $args['max_value'] = $product_max;
            }
        } else {
            $args['max_value'] = false;
        }
        if ($_product->managing_stock() && !$_product->backorders_allowed()) {
            $stock = $_product->get_stock_quantity();
            if ($args['max_value']) {
                $args['max_value'] = min($stock, $args['max_value']);
            } else {
                $args['max_value'] = $stock;
            }
        }
        $product_quantity = '<div class="quantity">
                                    <input type="button" value="-" class="minus">
                                    <input type="number" class="input-text qty text" step="1" min="' . $args['min_value'] . '" max="' . $args['max_value'] . '" name="cart[' . $cart_item_key . '][qty]" value="' . $cart_item['quantity'] . '" title="כמות" size="4" pattern="[0-9]*" inputmode="numeric">
                                    <input type="button" value="+" class="plus">
                                </div>';
        if ($product_max) {
            $product_quantity .= '<div class="max_qty_product">' . $product_max . ' items max</div>';
        }
    }
    return $product_quantity;
}

//Add product description to checkout & cart
//    add_filter( 'woocommerce_cart_item_name', 'customizing_cart_item_data', 10, 3);
//    function customizing_cart_item_data( $item_name, $cart_item, $cart_item_key ) {
//
//        $description = $cart_item['data']->get_description();
//        if( $cart_item['data']->is_type('variation') && empty( $description ) ){
//            $product = wc_get_product( $cart_item['data']->get_parent_id() );
//            $description = $product->get_description();
//        }
//
//        if( ! empty( $description )) {
//            $item_name .= '<span class="desc">' . $description . '</span>';
//        }
//
//        return $item_name;
//    }

//Product min max quantity
function variscite_wc_qty_add_product_field()
{
    echo '<div class="options_group">';
    woocommerce_wp_text_input(
        array(
            'id'          => '_wc_min_qty_product',
            'label'       => __('Minimum Quantity', 'woocommerce-max-quantity'),
            'placeholder' => '',
            'desc_tip'    => 'true',
            'description' => __('Optional. Set a minimum quantity limit allowed per order. Enter a number, 1 or greater.', 'woocommerce-max-quantity')
        )
    );
    echo '</div>';
    echo '<div class="options_group">';
    woocommerce_wp_text_input(
        array(
            'id'          => '_wc_max_qty_product',
            'label'       => __('Maximum Quantity', 'woocommerce-max-quantity'),
            'placeholder' => '',
            'desc_tip'    => 'true',
            'description' => __('Optional. Set a maximum quantity limit allowed per order. Enter a number, 1 or greater.', 'woocommerce-max-quantity')
        )
    );
    echo '</div>';
}
add_action('woocommerce_product_options_inventory_product_data', 'variscite_wc_qty_add_product_field');

function variscite_wc_qty_save_product_field($post_id)
{
    $val_min = trim(get_post_meta($post_id, '_wc_min_qty_product', true));
    $new_min = sanitize_text_field($_POST['_wc_min_qty_product']);
    $val_max = trim(get_post_meta($post_id, '_wc_max_qty_product', true));
    $new_max = sanitize_text_field($_POST['_wc_max_qty_product']);

    if ($val_min != $new_min) {
        update_post_meta($post_id, '_wc_min_qty_product', $new_min);
    }
    if ($val_max != $new_max) {
        update_post_meta($post_id, '_wc_max_qty_product', $new_max);
    }
}
add_action('woocommerce_process_product_meta', 'variscite_wc_qty_save_product_field');


function wc_get_product_max_limit($product_id)
{
    $qty = get_post_meta($product_id, '_wc_max_qty_product', true);
    if (empty($qty)) {
        $limit = false;
    } else {
        $limit = (int) $qty;
    }
    return $limit;
}
function wc_get_product_min_limit($product_id)
{
    $qty = get_post_meta($product_id, '_wc_min_qty_product', true);
    if (empty($qty)) {
        $limit = false;
    } else {
        $limit = (int) $qty;
    }
    return $limit;
}

//variscite_wc_update_cart_button
add_action('woocommerce_cart_collaterals', 'variscite_wc_update_cart_button');
function variscite_wc_update_cart_button()
{
    echo '<div class="update_cart_wrap"><a href="' . get_permalink(wc_get_page_id('shop')) . '" class="continue_shopping_mobile">' . __('Continue shopping', 'variscite') . '</a><button type="button" class="button update_cart_trigger"  value="Update cart">' . __("Update cart") . '</button></div>';
}


//variscite_wc_text_after_cart
add_action('woocommerce_after_cart', 'variscite_wc_text_after_cart');
add_action('woocommerce_review_order_after_submit', 'variscite_wc_text_after_cart');
function variscite_wc_text_after_cart()
{
    echo '<div class="mobile_paypal_description">
                <div class="lock_icon"></div>
                <div class="paypal_info">
                    <div class="paypal_text">' . __("Secure payment via PayPal. You can pay with your credit card if you don’t have a PayPal account") . '</div>
                    <div class="paypal_icon"></div>
                </div>
            </div>';
}

remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);


//Add 404 page menu
function register_404_menu()
{
    register_nav_menus(array(
        '404page-menu' => __('404 Page Menu', 'veriscite'),
    ));
}
add_action('after_setup_theme', 'register_404_menu');

// Add custom WIDGETS
function veriscite_widgets_init()
{

    register_sidebar(array(
        'name'          => __('404 Page widget', 'veriscite'),
        'id'            => '404page-widget',
        'description'   => '404 page widget area',
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget'  => '</div>',
        'before_title'  => '<h6 class="widget-title">',
        'after_title'   => '</h6>',
    ));
}
add_action('widgets_init', 'veriscite_widgets_init');


//Product category shortcode 404 page
function get_404_page_categories()
{
    $categories = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true, 'number' => 4, 'term_taxonomy_id' => array(19, 17, 18, 27)]);
    $template = '';
    foreach ($categories as $key => $value) {
        $cat_id   = $value->term_id;
        $cat_link = get_category_link($cat_id);

        $thumbnail_id = get_term_meta($value->term_id, 'thumbnail_id', true);
        $image = wp_get_attachment_url($thumbnail_id);

        $template .= '<div class="cat_item">';
        $template .=  '<div class="cat_image">';
        if ($image) {
            $template .= '<a href="' . $cat_link . '"><img src="' . $image . '" alt="" /></a>';
        }
        $template .= '</div>';
        $template .= '<div class="cat_name"><a href="' . $cat_link . '">' . $value->name . '</a></div>';
        $template .=  '</div>';
    }

    return $template;
}
add_shortcode('woo-products-categories-with-thumbnail', 'get_404_page_categories');

//Remove the tabs from woocommerce
add_filter('woocommerce_product_tabs', 'woo_remove_product_tabs', 98);
function woo_remove_product_tabs($tabs)
{

    unset($tabs['description']);
    unset($tabs['reviews']);
    unset($tabs['additional_information']);

    return $tabs;
}

// (@AsafA)
//show attributes after summary in product single view
add_action('woocommerce_before_add_to_cart_form', function () {
    global $product;

    $product_attributes = $product->get_attributes();

    $specifications = get_the_terms($product->get_id(), 'pa_specifications');
    $compatible_products = get_the_terms($product->get_id(), 'pa_compatible-products');
    $cpu_name = get_the_terms($product->get_id(), 'pa_cpu-name');

    $terms = wp_get_post_terms($product->get_id(), 'product_cat');
    foreach ($terms as $term) $categories[] = $term->slug;

    $template = '';
    if (in_array('evaluation-kit', $categories)) {
        $som_link = get_field('product_specifications_url');
        $template .= '<div class="som_links_wrap">';
        if ($som_link) {
            $template .= '<a href="' . $som_link . '" target="_blank" class="product_specification_link">' . __('SOM Specifications', 'variscite') . '</a>';
        }

        $kit_link = get_field('kit_specifications_url');
        if ($kit_link) {
            $template .= '<a href="' . $kit_link . '" target="_blank" class="product_specification_link">' . __('Kit Specifications', 'variscite') . '</a>';
        }

        $template .= '</div>';

        $template .= '<div class="mobile_gallery_contact_us">
                            ' . get_field('kit_contact_us_note', 'option') . '
                        </div>';
    }

    if ($specifications && isset($product_attributes['pa_specifications']) && $product_attributes['pa_specifications']->get_data()['visible']) {
        $template .= '<div class="attribute_title">Specification:</div>';

        $template .= '<ul class="attribute_list_specifications">';
        foreach ($specifications as $key => $specification) {
            $template .= '<li><span>' . $specification->name . ' </span>' . $specification->description . '</li>';
        }
        $template .=  '</ul>';
    }

    if ($compatible_products && isset($product_attributes['pa_compatible-products']) && $product_attributes['pa_compatible-products']->get_data()['visible']) {

        $template .= '<div class="attribute_title">Compatible products:</div>';
        $template .= '<ul class="attribute_list_compatible_products">';
        foreach ($compatible_products as $key => $c_product) {
            $template .= '<li><span>' . $c_product->name . ' </span>' . $c_product->description . '</li>';
        }
        $template .=  '</ul>';
    }

    if ($cpu_name && isset($product_attributes['pa_cpu-name']) && $product_attributes['pa_cpu-name']->get_data()['visible']) {
        $template .= '<ul class="attribute_list_cpu_name">';
        foreach ($cpu_name as $key => $cpu) {
            $template .= '<li><span>CPU Name: ' . $cpu->name . ' </span>' . $cpu->description . '</li>';
        }
        $template .=  '</ul>';
    } else {
        $template .= '<div class="summery-separation"></div>';
    }

    //    if ( in_array( 'system-on-module', $categories ) ){
    //        $template .= '<div class="som_linux">All SOMs are preloaded with Linux U-Boot</div>';
    //        $link = get_field('product_specifications_url');
    //        if( $link ){
    //            $template .= '<a href="'. $link .'" target="_blank" class="product_specification_link">Product specification</a>';
    //        }
    //    }

    if (in_array('power-supplies', $categories) && $product->is_type('variable')) {
        $link = get_field('product_specifications_url');
        if ($link) {
            $template .= '<a href="' . $link . '" target="_blank" class="product_specification_link">' . __("Product specification", "variscite") . '</a>';
        }
    }

    if (in_array('system-on-module', $categories)) {
        $product_som_contact_button_url = get_field('product_som_contact_button_url');
        $product_som_contact_button_url = $product_som_contact_button_url ? $product_som_contact_button_url : 'https://woocommerce-689526-4018639.cloudwaysapps.com/contact-us/';

        $hide_display_text = get_field('product_som_display_add_text');
        if ($hide_display_text !== true) {
            $product_som_add_text = '';
            $product_som_add_text_product = get_field('product_som_add_text');
            if ($product_som_add_text_product) {
                $product_som_add_text =  $product_som_add_text_product;
            } else {
                $product_som_add_text_option = get_field('product_som_add_text', 'option');
                if ($product_som_add_text_option) {
                    $product_som_add_text = $product_som_add_text_option;
                }
            }

            $product_som_add_text = $product_som_add_text ? $product_som_add_text : sprintf(__('Variscite offers limited configuration options on its online store. <a href="%s">Contact our sales</a> to optimize your cost & performance with customized SoM configuration in volumes as low as 25 units', 'variscite'), esc_url($product_som_contact_button_url));
            if ($product_som_add_text) {
                $template .= '<div class="product-som-add-text">' . $product_som_add_text . '</div>';
            }
        }

        $hide_display_contact_button = get_field('product_som_shipping_contact_button_display');
        if ($hide_display_contact_button === true) {
            if ($product_som_contact_button_url) {
                $template .= '<a href="' . $product_som_contact_button_url . '" target="_blank" class="product_specification_link product_contact_us_link">' . __("Contact Us", "variscite") . '</a>';
            }
        }

        $product_specifications_url = get_field('product_specifications_url');
        if ($product_specifications_url) {
            $template .= '<a href="' . $product_specifications_url . '" target="_blank" class="product_specification_link">' . __("Product specification", "variscite") . '</a>';
        }
    }

    if (get_field('variscite__product_is_not_purchasable')) {
        $template .= '<div class="product-contact-wrap contact_us_page">';
        $template .= '<span>' . get_field('variscite__product_custom_form_message') . '</span>';
        $template .= do_shortcode('[variscite-contact-us]');
        $template .= '</div>';
    }

    echo $template;
}, 5);

///Kit - adding accessories wrap
add_action('woocommerce_before_variations_form', 'accessories_wrap', 5);
function accessories_wrap()
{
    global $product;
    // (@AsafA) is som product category (som term_id = 18, 173 or slug system-on-module)
    $is_som_cat = has_term('system-on-module', 'product_cat', $product->get_id());
    if ($is_som_cat) {
        // $ships_time = get_field('shipping_within_time');
        // if( $ships_time ){
        //     echo '<div class="ships_info">'. $ships_time .'</div>';
        // }

        $product_variations_title = get_field('product_variations_title');
        $product_variations_title = $product_variations_title ? $product_variations_title : __('Stock configurations for Evaluation and Prototype', "variscite");
        if ($product_variations_title) {
            echo '<h2 class="product-variations-title">' . $product_variations_title . '</h2>';
        }

        $hide_ships_time_display = get_field('produc_som_shipping_message_display');
        if ($hide_ships_time_display !== true) {
            $produc_som_shipping_message_content = get_field('produc_som_shipping_message_content');
            $produc_som_shipping_message_content = $produc_som_shipping_message_content ? $produc_som_shipping_message_content : __('Ships within 4 working days', "variscite");
            echo '<div class="ships_info">' . $produc_som_shipping_message_content . '</div>';
        }
    }
    $is_edit = false;

    if (isset($_GET['attribute_pa_kit']) && isset($_GET['attribute_pa_som-configuration']) && isset($_GET['attribute_pa_operating-system'])) {
        $is_edit = true;
    }

    $terms = wp_get_post_terms($product->get_id(), 'product_cat');

    foreach ($terms as $term) $categories[] = $term->slug;

    if (in_array('evaluation-kit', $categories)) {
        echo '<div class="product_accessories' . ($is_edit ? ' visited' : '') . '"></div>';
    }
}

//Removing the Product Meta ‘Categories’ in a Product Page – WooCommerce
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);

//add_content_after_addtocart_button_func
add_action('woocommerce_after_add_to_cart_button', 'add_content_after_addtocart_button_func');
function add_content_after_addtocart_button_func()
{
    global $post;
    global $product;
    $test = $product->get_data();

    $terms = wp_get_post_terms($post->ID, 'product_cat');
    $curr =  get_woocommerce_currency($product->get_id());


    $price = $test['price'];

    foreach ($terms as $term) $categories[] = $term->slug;

    echo '<input type="hidden" id="item_type" name="item_type" value="' . $terms[0]->name . '">';
    echo '<input type="hidden" id="item_price" name="item_price" value="' . $price . '">';
    echo '<input type="hidden" id="item_curr" name="item_curr" value="' . $curr . '">';

    if (!in_array('evaluation-kit', $categories) && !in_array('system-on-module', $categories) && $product->is_type('simple')) {
        $link = get_field('product_specifications_url');

        if ($link) {
            echo '<a href="' . $link . '" target="_blank" class="product_specification_link">' . __("Product specification", "variscite") . '</a>';
        }
    }
}

// (@AsafA) variation_radio_buttons
function variation_radio_buttons($html, $args)
{

    global $product;

    $prod_variations = $product->get_available_variations();
    $variations_organized = array();

    foreach ($prod_variations as $prod_variation) {
        $variations_organized[$prod_variation['attributes']['attribute_pa_som-configuration']] = $prod_variation;
    }

    $args = wp_parse_args(apply_filters('woocommerce_dropdown_variation_attribute_options_args', $args), array(
        'options'          => false,
        'attribute'        => false,
        'product'          => false,
        'selected'         => false,
        'name'             => '',
        'id'               => '',
        'class'            => '',
        'show_option_none' => __('Choose an option', 'woocommerce'),
    ));

    if (false === $args['selected'] && $args['attribute'] && $args['product'] instanceof WC_Product) {
        $selected_key     = 'attribute_' . sanitize_title($args['attribute']);
        $args['selected'] = isset($_REQUEST[$selected_key]) ? wc_clean(wp_unslash($_REQUEST[$selected_key])) : $args['product']->get_variation_default_attribute($args['attribute']);
    }

    $options               = $args['options'];
    $product               = $args['product'];
    $attribute             = $args['attribute'];
    $name                  = $args['name'] ? $args['name'] : 'attribute_' . sanitize_title($attribute);
    $id                    = $args['id'] ? $args['id'] : sanitize_title($attribute);
    $class                 = $args['class'];
    $show_option_none      = (bool)$args['show_option_none'];
    $show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : __('Choose an option', 'woocommerce');

    if (empty($options) && !empty($product) && !empty($attribute)) {
        $attributes = $product->get_variation_attributes();
        $options    = $attributes[$attribute];
    }


    $product_cat = wp_get_post_terms($product->get_id(), 'product_cat');
    foreach ($product_cat as $cat) $categories[] = $cat->slug;

    // (@AsafA)
    $is_som_cat = in_array('system-on-module', $categories);

    if (in_array('evaluation-kit', $categories)) {
        $parent_product_type = 'kit';
    } else if (in_array('system-on-module', $categories) || in_array('system-on-module-de', $categories)) {
        $parent_product_type = 'som';
    } else {
        $parent_product_type = 'accessory';
    }

    $is_som = false;
    $is_kit = false;

    // (@AsafA)
    $radios = '';
    $radios_builder = [];
    $som_special_offer_radio = '';
    $term_name_tor_replace = '';
    $display_som_special_offer = get_field('product_display_som_special_offer', $product->get_id());
    $product_som_special_offer_quantity = product_som_special_offer_quantity($product->get_id());
    $product_display_som_special_offer_product_variation_item = get_field('product_display_som_special_offer_product_variation_item', $product->get_id());
    $display_som_special_offer_product_variation = '';
    $display_som_special_offer_product_variation_before_title = '';
    if ($display_som_special_offer) {
        $display_som_special_offer_product_variation = get_field('product_display_som_special_offer_product_variation', $product->get_id());
        $display_som_special_offer_product_variation_before_title = get_field('product_display_som_special_offer_product_variation_before_title', $product->get_id());
        $display_som_special_offer_product_variation_before_title = $display_som_special_offer_product_variation_before_title ? $display_som_special_offer_product_variation_before_title : __('Baseline Configuration', "variscite");
    }
    $radios_start = '<div class="variation-radios ' . $attribute . ($is_som_cat ? ' variation-radios-cards' : '') . '">';

    if (strpos($attribute, 'som-config') !== false) {
        $is_som = true;
    } else if (strpos($attribute, 'kit') !== false) {
        $is_kit = true;
    }

    if (!empty($options)) {

        if ($product && taxonomy_exists($attribute)) {

            $terms = wc_get_product_terms($product->get_id(), $attribute, array(
                'fields' => 'all',
            ));

            foreach ($terms as $term) {

                if (in_array($term->slug, $options, true)) {

                    $images = get_field('kit_gallery', $attribute . '_' . $term->term_id);
                    $product_accessories = get_field('product_accessories', $attribute . '_' . $term->term_id);
                    if ($product_accessories) {
                        $product_accessories = implode(",", $product_accessories);
                    }
                    $term_desc = $term->description;
                    $ul_html = '';
                    if ($term_desc != '') {
                        $term_desc = str_replace('&amp;', '&', $term_desc);
                        $term_desc_array = explode(";", $term_desc);
                        $ul_html  .= '<ul>';
                        foreach ($term_desc_array as $key => $desc_item) {
                            // (@AsafA)
                            $ul_html .= ($is_som_cat && ($key % 5 == 0) && $key > 0 && count($term_desc_array) > 5 ? '</ul><ul>' : '');
                            $ul_html .= '<li>' . $desc_item . '</li>';
                        }
                        $ul_html .= '</ul>';
                    }

                    if (strpos($term->name, 'Development Kit') !== false) {
                        $term_name = 'Development Kit';
                    } else if (strpos($term->name, 'Starter Kit') !== false) {
                        $term_name = 'Starter Kit';
                    } else {
                        $term_name = $term->name;
                    }

                    // (@AsafA)
                    if ($display_som_special_offer_product_variation ==  $term->term_id && $is_som_cat && $display_som_special_offer) {
                        $term_name_tor_replace =  $term_name;
                    }

                    // (@AsafA)
                    $radios_builder[$term->term_id][] = '
                    <div class="variation_item">
                        ' . ($is_som_cat ? '<span class="term_name">' . $term_name . '</span>' : '') . '
                        <div class="variation_item_list">
                        ' . ($is_som_cat ? '' : '<label for="' . esc_attr($term->slug) . '"><input type="radio" data-accessories="' . $product_accessories . '" id="' . esc_attr($term->slug) . '" name="' . esc_attr($name) . '" value="' . esc_attr($term->slug) . '" ' . checked(sanitize_title($args['selected']), $term->slug, false) . '><span class="term_name" ' . $term->term_id . '>' . $term_name . '</span>');

                    $term_price = '';
                    if ($is_som) {
                        if ($parent_product_type == 'kit') {
                            $var_som_price = (int)get_field('variscite_attribute_price', 'pa_som-configuration_' . $term->term_id);

                            if ($var_som_price != 0) {
                                $term_price .= '<span class="term-price">' . get_woocommerce_currency_symbol() . $var_som_price . '</span>';
                            }
                        }
                    }

                    if ($is_som && $parent_product_type == 'som') {
                        $var_som_price = (int)$variations_organized[esc_attr($term->slug)]['display_price'];

                        if ($var_som_price != 0) {
                            $term_price .= '<span class="term-price">' . get_woocommerce_currency_symbol() . $var_som_price . '</span>';
                        }
                    }

                    // (@AsafA)
                    if (!$is_som_cat) {
                        $radios_builder[$term->term_id][] = $term_price;
                    }

                    /* if($is_som_cat) {
                        $radios .= '<div class="title-tooltip">
                                    <figure></figure>
                                    <span>'.__("The price in the online store indicates low quantity stock items pricing, excluding shipment.
                                For pricing of higher quatities and optimized SOM configuration, please contact us.", "Variscite-kit").'</span>
                                </div>';
                    } */

                    $radios_builder[$term->term_id][] = '<div class="list_term_desc">' . $ul_html . '</div>';

                    if ($is_kit) {
                        $kit_price = (int)get_field('variscite_attribute_price', 'pa_kit_' . $term->term_id);

                        if ($kit_price !== 0) {
                            $radios_builder[$term->term_id][] = '<span class="kit-price">' . __("Price:", "Variscite-kit") . ' ' . get_woocommerce_currency_symbol() . $kit_price . '</span>';
                        }
                    }


                    // (@AsafA)
                    if ($is_som_cat) {
                        $radios_builder[$term->term_id][] = '
                        <div class="item_price">';
                        // price
                        $radios_builder[$term->term_id][] = $term_price . '<span class="per-unit">' . __("Per Unit", "Variscite-som") . '</span>';
                        // button
                        // $radios .= '<label class="button variation-radios-card-button" for="'.esc_attr($term->slug).'">' . esc_html( $product->single_add_to_cart_text() ) .
                        //                 '<input type="radio" data-accessories="'. $product_accessories .'" id="'.esc_attr($term->slug).'" name="'.esc_attr($name).'" value="'.esc_attr($term->slug).'" '.checked(sanitize_title($args['selected']), $term->slug, false).'>
                        //             </label>';
                        $add_to_cart_url = wc_get_cart_url();
                        $add_to_cart_url = add_query_arg(array(
                            'add-to-cart' => $product->get_id(),
                            'variation_id' => $variations_organized[esc_attr($term->slug)]['variation_id'],
                            esc_attr($name) => esc_attr($term->slug),
                            'quantity' => 1,
                        ), $add_to_cart_url);

                        $radios_builder[$term->term_id][] = '<a class="button variation-radios-card-button" href="' . $add_to_cart_url . '">' . esc_html($product->single_add_to_cart_text()) . '</a>';
                        $radios_builder[$term->term_id][] = '</div>
                        </div> <!-- variation_item_list -->';
                    } else {
                        $radios_builder[$term->term_id][] = '</label></div> <!-- variation_item_list -->';
                    }

                    // (@AsafA)
                    if ($images && !$is_som_cat) {
                        $radios_builder[$term->term_id][] = '<div class="kit_gallery_wrap">
                            <div class="kit_gallery slider">';
                        foreach ($images as $image) {
                            $img = isset($image['ID']) ? wp_get_attachment_image($image['ID'], 'full') : '';
                            $imgsrc =  isset($image['ID']) ? wp_get_attachment_url($image['ID']) : '';
                            $radios_builder[$term->term_id][] = '<div class="image_container">' . ($img ? $img : '') . '<div class="gray_background" data-image-src="' . ($imgsrc ? $imgsrc : '') . '"><i class="fa fa-search-plus" aria-hidden="true"></i></div></div>';
                        }
                        $radios_builder[$term->term_id][] = '
                            </div>
                        </div>';
                    }

                    // use it in foreach loop $radios_builder[$term->term_id][] = '</div> <!-- variation_item -->';
                }
            }

            // (@AsafA)
            if (!empty($radios_builder)) {
                foreach ($radios_builder as $term_id => $radios_builder_arr) {
                    foreach ($radios_builder_arr as $radios_html) {
                        if (
                            $display_som_special_offer_product_variation == $term_id &&
                            $is_som_cat &&
                            $display_som_special_offer &&
                            $product_display_som_special_offer_product_variation_item !== true
                        ) {
                            // dont display the item
                        } else {
                            $radios .= $radios_html;
                        }
                    }
                    $radios .= '</div> <!-- variation_item -->';
                }

                foreach ($radios_builder as $term_id_sf => $radios_builder_arr_sf) {
                    if ($display_som_special_offer_product_variation == $term_id_sf && $is_som_cat && $display_som_special_offer) {
                        foreach ($radios_builder_arr_sf as $radios_html_sf) {
                            $som_special_offer_radio .=  $radios_html_sf;
                        }

                        // $som_special_offer_radio = str_replace('&quantity=1', '&quantity=' . $product_som_special_offer_quantity, $som_special_offer_radio) . '
                        //     <div class="spesial-offer-bottom variation_item_list">
                        //         <div class="spesial-offer-bottom-text">For more configurations for production quantities, contact our sales team.</div>
                        //         <a href="/contact-us" target="_blank" class="button variation-radios-card-button button-border">' . __("Contact Us" , "variscite"). '</a>
                        //     </div>
                        // </div> <!-- variation_item -->';
                        $som_special_offer_radio = str_replace('&quantity=1', '&quantity=' . $product_som_special_offer_quantity, $som_special_offer_radio) . '
                        </div> <!-- variation_item -->';
                        $som_special_offer_radio = str_replace($term_name_tor_replace, '<span>' . $display_som_special_offer_product_variation_before_title . ':</span> ' . $term_name_tor_replace, $som_special_offer_radio);
                    }
                }
            }
        } else {
            foreach ($options as $option) {
                $checked    = sanitize_title($args['selected']) === $args['selected'] ? checked($args['selected'], sanitize_title($option), false) : checked($args['selected'], $option, false);
                $radios    .= '<input type="radio" name="' . esc_attr($name) . '" value="' . esc_attr($option) . '" id="' . sanitize_title($option) . '" ' . $checked . '><label for="' . sanitize_title($option) . '">' . esc_html(apply_filters('woocommerce_variation_option_name', $option)) . '</label>';
            }
        }
    }

    $radios_end = '</div> <!-- variation-radios -->';

    $radios_html = $radios_start . $radios . $radios_end;

    // Som Special Offer
    if (!empty($som_special_offer_radio)) {
        $numbertext = number_format($product_som_special_offer_quantity);
        $htwo_text = sprintf(__('Special Offer for %s Units', 'variscite'), $numbertext);
        $som_special_offer_html = '<div class="som-special-offer">' .
            $radios_start . '<h2>' . $htwo_text . '</h2>' . $som_special_offer_radio . $radios_end
            . '</div>';
        $radios_html .= $som_special_offer_html;
    }

    //    if ( in_array( 'evaluation-kit', $categories ) ){
    //        $radios .= '<div class="evaluation_kit_next_step '.$attribute.'"><div class="next_button">Next</div></div>';
    //    }

    return $html . $radios_html;
}
add_filter('woocommerce_dropdown_variation_attribute_options_html', 'variation_radio_buttons', 20, 2);

// Update WooCommerce Flexslider options
add_filter('woocommerce_single_product_carousel_options', 'variscite_update_woo_flexslider_options');
function variscite_update_woo_flexslider_options($options)
{
    $options['directionNav'] = true;
    return $options;
}

//Kit - product accessories
add_action('wp_ajax_product_accessories', 'product_accessories_callback');
add_action('wp_ajax_nopriv_product_accessories', 'product_accessories_callback');
function product_accessories_callback() {
    global $woocommerce;
    $products_html = '';
    $accessories_array = explode(",", $_POST['product_accessories']);

    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 10,
        'post__in' => $accessories_array
    );

    $loop = new WP_Query($args);

    if ($loop->have_posts()) {
        ob_start();
        echo '<div class="accessories_header_wrap">' . __("Add accessories to your Kit", "variscite") . '</div>';
        echo '<div class="accessories_products_wrap">';

        while ($loop->have_posts()) {
            $loop->the_post();
            $_product = wc_get_product(get_the_ID());
            $product_thumbnail = get_field('product_shop_page_image', get_the_ID()) ? get_field('product_shop_page_image', get_the_ID())['url'] : wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), 'single-post-thumbnail')[0];

            echo '<div class="accessories_product_item">';
            echo '<div class="accessories_product_image_wrap">';
            echo '<img src="' . $product_thumbnail . '" alt="' . get_the_title() . '" />';
            echo '</div>';

            $product_id = get_the_ID();
            if ($_product->is_type('variable')) {
                $vars = $_product->get_variation_attributes();
                $var_name = array_keys($vars)[0];
                $var_options = $vars[$var_name];
                $selected = ($_product->get_variation_default_attribute($var_name) ? $_product->get_variation_default_attribute($var_name) : $var_options[0]);
                $product_id .= ',' . $selected . ',attribute_' . $var_name;
            }

            echo '<div class="accessories_product_desc_wrap">';
            echo '<div class="checkbox-wrap">';
            echo '<input type="checkbox" class="custom_product_accessory" name="' . get_the_ID() . '_product-accessories-variations" id="product-' . get_the_ID() . '" data-product-id="' . get_the_ID() . '" value="' . $product_id . '" data-name="' . get_the_title() . '">';
            echo '<label for="product-' . get_the_ID() . '"><span class="custom_checkbox"></span>';
            echo '<div class="prod_title">' . get_the_title() . '</div>';
            $regular_price = $_product->is_type('variable') ? $_product->get_variation_regular_price('min', true) : $_product->get_regular_price();
            echo '<h2 class="price">' . get_woocommerce_currency_symbol() . '<strong class="amount">' . floatval($regular_price) . '</strong></h2>';
            echo  '</label>';
            echo  '</div>';
            echo '<p>' . get_accessory_accordion_description(get_the_ID()) . '</p>';

            if ($_product->is_type('variable')) {
                echo '<div class="accessory-attributes">';
                foreach ($_product->get_variation_attributes() as $attribute_name => $options) {
                    echo '<td class="label"><label for="' . sanitize_title($attribute_name) . '">' . wc_attribute_label($attribute_name) . '</label></td>';
                    echo '<td class="value">';
                    $selected = ($_product->get_variation_default_attribute($attribute_name) ? $_product->get_variation_default_attribute($attribute_name) : '');
                    wc_dropdown_variation_attribute_options(array('options' => $options, 'attribute' => $attribute_name, 'product' => $_product, 'selected' => $selected));
                    echo '</td>';
                }
                echo '<span class="variation-error">Choose ' . wc_attribute_label($attribute_name) . '</span>';
                echo '</div>';
            }

            echo '<p style="margin-top: 10px;"><a href="' . get_permalink(get_the_ID()) . '" target="_blank" rel="noopener">' . __("View", "variscite") . ' &gt;</a></p>';
            echo '</div>';
            echo '</div>';
        }

        echo '</div>';
        $products_html .= ob_get_clean();
        ob_end_flush();

        $json_obj = array('products' => $products_html);
        wp_send_json($json_obj);
    }
    wp_die();
}


//  Image PopUp
add_action('woocommerce_after_single_product', 'show_image_popup', 5);
function show_image_popup()
{

    echo '<div class="modal fade kit_modal_popup" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
              <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        
                      </div>
                      <div class="modal-body" style="text-align: center;">
                        
                      </div>
                </div>
              </div>
            </div>';
}

// Function to add kit accessories to cart, upon submission
add_action('woocommerce_add_to_cart', 'add_kit_accessories_to_cart', 10, 2);
function add_kit_accessories_to_cart()
{
    $postdata = $_POST;

    foreach ($postdata as $post_data_key => $post_datum) {

        // If the field name contains the 'product-accessories-variations' key, add the value to the cart
        if (strpos($post_data_key, 'product-accessories-variations') !== false) {

            $accessory_data = explode(',', $post_datum);
            $product_id = $accessory_data[0];

            // Check if product has attribute key
            if (isset($accessory_data[1])) {
                $attribute_option = $accessory_data[1];
                $attribute = $accessory_data[2];

                $variation_arr = array($attribute => $attribute_option);

                $variation_id = find_matching_product_variation_id(
                    $product_id,
                    $variation_arr
                );

                // Since add_to_cart() triggers the woocommerce_add_to_cart hook,
                // remove this action before adding the accessory to the cart
                remove_action('woocommerce_add_to_cart', __FUNCTION__);
                WC()->cart->add_to_cart((int)$product_id, 1, (int)$variation_id, $variation_arr);
            } else {

                // Since add_to_cart() triggers the woocommerce_add_to_cart hook,
                // remove this action before adding the accessory to the cart
                remove_action('woocommerce_add_to_cart', __FUNCTION__);
                WC()->cart->add_to_cart((int)$product_id);
            }
        }
    }
}

function find_matching_product_variation_id($product_id, $attributes)
{
    return (new \WC_Product_Data_Store_CPT())->find_matching_product_variation(
        new \WC_Product($product_id),
        $attributes
    );
}

///// Is Mobile Helper //////
function isMobile()
{
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}

/**
 * Add est. product quantities field to the checkout
 */
add_action('woocommerce_after_order_notes', 'estimated_product_quantities_field');
function estimated_product_quantities_field($checkout)
{

    echo '<div id="estimated_product_quantities">
        <h2>' . __('Estimated project Quantities') . '
        <div class="title-tooltip">
            <figure></figure>
            <span>' . __('Select the estimated project size. This field does not affect the number of products in this purchase or future purchases', 'variscite') . '</span>
        </div></h2>';

    if (isMobile()) {
        $estimated_product_values = array(
            '1-100' => '1-100',
            '100-500' => '100-500',
            '500-1000' => '500-1000',
            '1000-3000' => '1000-3000',
            '3000-5000' => '3000-5000',
            '>5000' => '>5000',
        );
    } else {
        $estimated_product_values = array(
            '1-100' => '1-100',
            '500-1000' => '500-1000',
            '3000-5000' => '3000-5000',
            '100-500' => '100-500',
            '1000-3000' => '1000-3000',
            '>5000' => '>5000',
        );
    }

    woocommerce_form_field('estimated_product_quantities', array(
        'type'          => 'radio',
        'class'         => array('my-field-class form-row-wide'),
        'label'         => '',
        'id'            => 'estimated_product_quantities',
        'options'       => $estimated_product_values,
    ), $checkout->get_value('estimated_product_quantities'));

    echo '</div>';
}

// Fix postmeta form limit
add_filter('postmeta_form_limit', function ($limit) {
    return 50;
});

// Save the field on checkout
function save_projected_quantities_checkout_field($order_id, $posted)
{

    if (isset($_POST['estimated_product_quantities'])) {
        update_post_meta($order_id, '_estimated_product_quantities', $_POST['estimated_product_quantities']);
    }
}
add_action('woocommerce_checkout_update_order_meta', 'save_projected_quantities_checkout_field', 10, 2);

// Display the field in the summary of the order
function display_est_quantities_in_order_data($order)
{

    if (is_object($order)) {
        $order_id = $order->get_id();
    } else {
        $order_id = $order;
    }

    ?>
    <table class="shop_table shop_table_responsive additional_info">
        <tbody>
            <tr>
                <th><?php _e('Estimated Project Quantities:'); ?></th>
                <td><?php echo get_post_meta($order_id, '_estimated_product_quantities', true); ?></td>
            </tr>
        </tbody>
    </table>
<?php }
add_action('woocommerce_order_details_after_order_table', 'display_est_quantities_in_order_data', 10);
add_action('woocommerce_view_order', 'display_est_quantities_in_order_data', 10);

// Display the field in the admin
function display_est_quantities_order_data_in_admin($order)
{  ?>
    <div class="order_data_column">

        <h4><?php _e('Extra Information', 'woocommerce'); ?><a href="#" class="edit_address"><?php _e('Edit', 'woocommerce'); ?></a></h4>
        <div class="address">
            <?php
            echo '<p><strong>' . __('Estimated Project Quantities') . ':</strong>' . get_post_meta($order->id, '_estimated_product_quantities', true) . '</p>'; ?>
        </div>
        <div class="edit_address">
            <?php woocommerce_wp_text_input(array('id' => '_estimated_product_quantities', 'label' => __('Estimated Project Quantities'), 'wrapper_class' => '_billing_company_field')); ?>
        </div>

        <div class="address">
            <?php
            echo '<p><strong>' . __('Company Registration Number') . ':</strong>' . get_post_meta($order->id, '_billing_company_reg_number', true) . '</p>'; ?>
        </div>
        <div class="edit_address">
            <?php woocommerce_wp_text_input(array('id' => '_billing_company_reg_number', 'label' => __('Company Registration Number'), 'wrapper_class' => '_billing_company_reg_number')); ?>
        </div>
    </div>
    <?php }
add_action('woocommerce_admin_order_data_after_order_details', 'display_est_quantities_order_data_in_admin');

// Allow editing the field in the admin
function save_est_quantities_order_data_in_admin($post_id, $post)
{
    update_post_meta($post_id, '_estimated_product_quantities', wc_clean($_POST['_estimated_product_quantities']));
}
add_action('woocommerce_process_shop_order_meta', 'save_est_quantities_order_data_in_admin', 45, 2);

// Add alternative coupon code field
add_action('woocommerce_checkout_shipping', 'alternative_coupon_code_field');
function alternative_coupon_code_field($checkout)
{

    if (wc_coupons_enabled()) {

        $applied_coupons = WC()->cart->get_applied_coupons();

        echo '<div id="woocommerce_promo_code">
            <h2>' . __('Promotional Code', 'Variscite') . '</h2>';

        echo '<div class="woocommerce_promo_code_inner">';

        woocommerce_form_field('promo_code', array(
            'type'          => 'text',
            'class'         => array('promo-code form-row-wide'),
            'label'         => '',
        ), !empty($applied_coupons) ? esc_html($applied_coupons[0]) : '');

        echo '<button>' . __('Apply', 'Variscite') . '</button></div>';
        echo '</div>';
    }
}

// Rename default WooCommerce strings
function wc_billing_field_strings($translated_text, $text, $domain)
{

    switch ($translated_text) {
        case 'Billing details':
            $translated_text = __('Billing Information', 'woocommerce');
            break;

        case 'Place order':
            $translated_text = __('Proceed to Payment', 'woocommerce');
            break;

        case 'Apply coupon':
            $translated_text = __('Apply', 'woocommerce');
            break;

        case 'See options':
            $translated_text = __('Order', 'woocommerce');
            break;
    }

    return $translated_text;
}
add_filter('gettext', 'wc_billing_field_strings', 20, 3);

// Move place order button to bottom of checkout fields
function output_payment_button()
{
    $order_button_text = apply_filters('woocommerce_order_button_text', __('Place order', 'woocommerce'));

    // Terms and conditions
    echo '<div class="submit-and-terms"><div class="woocommerce-terms-and-conditions-wrapper">';
    if (wc_terms_and_conditions_checkbox_enabled()) :
    ?>

        <p class="form-row validate-required">
            <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
                <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="terms" <?php checked(apply_filters('woocommerce_terms_is_checked_default', isset($_POST['terms'])), true); // WPCS: input var ok, csrf ok. 
                                                                                                                                    ?> id="terms" />
                <span></span>

                <span class="woocommerce-terms-and-conditions-checkbox-text"><?php wc_terms_and_conditions_checkbox_text(); ?></span>
                <span class="required">*</span>
            </label>

            <input type="hidden" name="terms-field" value="1" />
        </p>

    <?php
    endif;

    // Privacy Policy field
    ?>

    <p class="form-row privacy-policy-wrap">
        <label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
            <input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="privacy-policy" <?php checked(apply_filters('woocommerce_terms_is_checked_default', isset($_POST['privacy-policy'])), true); ?> value="<?php echo date('c', time()); ?>" id="privacy-policy" />
            <span></span>

            <span class="woocommerce-terms-and-conditions-checkbox-text privacy-policy-text"><?php _e('I’ve read and accepted the <a href="https://www.variscite.com/privacy-policy/" target="_blank">privacy policy</a>', 'variscite'); ?></span>
        </label>
    </p>

<?php
    do_action('checkout_after_privacy_policy');

    // Checkout button
    echo '<input type="submit" class="button alt" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr($order_button_text) . '" data-value="' . esc_attr($order_button_text) . '" />';
    echo '</div></div>';
}
add_action('woocommerce_checkout_shipping', 'output_payment_button');

function remove_woocommerce_order_button_html()
{
    return '';
}
add_filter('woocommerce_order_button_html', 'remove_woocommerce_order_button_html');

function remove_woocommerce_checkout_terms()
{
    return '';
}
add_filter('woocommerce_checkout_show_terms', 'remove_woocommerce_checkout_terms');

// Add checkout custom notes field
function add_custom_notes_field_to_checkout($fields)
{

    // Remove the old Order Notes field
    unset($fields['order']['order_comments']);

    // Define custom Order Notes field data array
    $customer_note = array(
        'type' => 'textarea',
        'class' => array('form-row-wide', 'notes'),
        'label' => __('Order Notes', 'woocommerce'),
        'placeholder' => _x('Notes about your order, e.g. special notes for delivery.', 'placeholder', 'woocommerce')
    );

    // Set custom Order Notes field
    $fields['billing']['billing_customer_note'] = $customer_note;

    // Add the address 2 field back
    $address_2 = array(
        'type' => 'text',
        'class' => array('form-row-wide'),
        'label' => __('', 'woocommerce'),
        'required' => false,
        'placeholder' => _x('Address Line 2', 'placeholder', 'woocommerce')
    );

    // Set custom Order Notes field
    $fields['billing']['billing_address_2'] = $address_2;
    $fields['shipping']['shipping_address_2'] = $address_2;

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'add_custom_notes_field_to_checkout', 10, 1);

// Set the custom field 'billing_customer_note' in the order object as a default order note (before it's saved)
add_action('woocommerce_checkout_create_order', 'customizing_checkout_create_order', 10, 2);
function customizing_checkout_create_order($order, $data)
{
    $order->set_customer_note(isset($data['billing_customer_note']) ? $data['billing_customer_note'] : '');
}

// Add ajax call for coupons from cart
function apply_cart_coupon_code()
{
    $code = sanitize_text_field($_POST['code']);

    $coupon = new WC_Coupon($code);
    $discounts = new WC_Discounts(WC()->cart);

    $is_valid = $discounts->is_coupon_valid($coupon);

    if ($is_valid === true) {
        WC()->cart->apply_coupon($code);
        WC()->cart->calculate_totals();

        die(json_encode(array(
            'is_valid' => true
        )));
    } else {
        die(json_encode(array(
            'is_valid' => false,
            'error'    => $is_valid->errors['invalid_coupon']
        )));
    }
}
add_action('wp_ajax_apply_cart_coupon_code', 'apply_cart_coupon_code');
add_action('wp_ajax_nopriv_apply_cart_coupon_code', 'apply_cart_coupon_code');

// Add breadcrumbs to cart and checkout
add_action('generate_before_content', 'add_yoast_breadcrumbs');
function add_yoast_breadcrumbs()
{
    if (function_exists('yoast_breadcrumb') && (is_checkout() || is_cart())) {
        yoast_breadcrumb('<div id="breadcrumbs">', '</div>');
    }
}

// Add PayPal disclaimer before order review and cart
function paypal_disclaimer_before_review()
{
    if (is_cart()) {
        echo '<span class="pay_pal">' . __("Secure payment via PayPal. You can pay with your credit card if you don’t have a PayPal account") . '</span>';
    } elseif (is_checkout()) {
        echo '<div class="afterEntry">
         <span class="pay_pal">' . __("Secure payment via PayPal. You can pay with your credit card if you don’t have a PayPal account") . '</span>
		 </div>';
    }
}
add_action('variscite_after_main_title', 'paypal_disclaimer_before_review');

// Replace shop images with ACF
function replacing_template_loop_product_thumbnail()
{

    // Remove product images from the shop loop
    remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10);

    // Adding something instead
    function wc_template_loop_product_replaced_thumb()
    {
        global $post;

        $product_thumbnail = get_field('product_shop_page_image', $post->ID) ? get_field('product_shop_page_image', $post->ID)['url'] : wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'single-post-thumbnail')[0];

        echo '<img width="93" height="140" src="' . $product_thumbnail . '" alt="' . $post->post_title . '">';
    }

    add_action('woocommerce_before_shop_loop_item_title', 'wc_template_loop_product_replaced_thumb', 10);
}
add_action('woocommerce_init', 'replacing_template_loop_product_thumbnail');

// Manipulate product info in cart, checkout and thank you page
add_action('woocommerce_before_calculate_totals', 'manipulate_product_data_in_list', 10, 1);
function manipulate_product_data_in_list($cart)
{

    if (is_admin() && !defined('DOING_AJAX'))
        return;

    if (did_action('woocommerce_before_calculate_totals') >= 2)
        return;

    // Loop through cart items
    foreach ($cart->get_cart() as $cart_item) {

        // Get an instance of the WC_Product object
        $product = $cart_item['data'];
        $product_type = determine_product_type($product);

        // (1) Update the product's name
        // Get the product name (Added Woocommerce 3+ compatibility)
        $original_name = method_exists($product, 'get_name') ? $product->get_name() : $product->post->post_title;

        // SET THE NEW NAME
        if ($product_type === 'kit') {
            //$kit_type = $product->attributes['pa_kit'];
            $kit_type = $product->get_attribute('pa_kit');
            $original_name = explode(' ', $original_name)[0];

            if (strpos($kit_type, 'starter-kit') !== false) {
                $kit_type = 'Starter Kit';
            } else {
                $kit_type = 'Development Kit';
            }

            $new_name = $original_name . ' ' . $kit_type;
        } else if ($product_type === 'som') {
            $new_name = explode(' - ', $original_name)[0];
        } else {
            $new_name = $original_name;
        }

        // Set the new name (WooCommerce versions 2.5.x to 3+)
        if (method_exists($product, 'set_name')) {
            $product->set_name($new_name);
        } else {
            $product->post->post_title = $new_name;
        }
    }
}

// Alter product variations and data
function add_cart_extra_som_and_kit_info($item_data, $cart_item)
{

    $product = $cart_item['data'];
    $product_type = determine_product_type($product);

    $final_item_data = array();

    // Add Part Number (= PN, = SKU) to product data
    $pn = $product->get_sku();

    if ($product_type === 'kit') {
        $attributes = $product->get_attributes();

        // Included SOM
        //$som_slug = $product->attributes['pa_som-configuration'];
        $som_slug = $product->get_attribute('pa_som-configuration');

        if (isset($_GET['ddgg'])) {
            $final_item_data[] = array(
                'key' => 'test',
                'value' => print_r($attributes, true)
            );
        }

        // Operating System
        //$os = $product->attributes['pa_operating-system'];
        $os = $product->get_attribute('pa_operating-system');

        // SOM Part Number
        $som_pn = '';
        if (isset($attributes['pa_som-configuration'])) {
            $included_som = get_term_by('slug', $attributes['pa_som-configuration'], 'pa_som-configuration');
            $som_pn = get_field('system_on_module_pn', 'pa_som-configuration_' . $included_som->term_id);
        }

        $final_item_data[] = array(
            'key' => 'Kit PN',
            'value' => $pn
        );

        $final_item_data[] = array(
            'key' => 'Included SOM',
            'value' => $som_slug
        );

        $final_item_data[] = array(
            'key' => 'SOM PN',
            'value' => $som_pn
        );

        $final_item_data[] = array(
            'key' => 'Operating System',
            'value' => $os
        );
    } else if ($product_type === 'som') {

        //$som_slug = $product->attributes['pa_som-configuration'];
        $som_slug = $product->get_attribute('pa_som-configuration');;
        //$included_som = get_term_by('slug', $som_slug, 'pa_som-configuration');

        $final_item_data[] = array(
            'key' => 'SOM PN',
            'value' => $pn
        );

        $final_item_data[] = array(
            'key' => 'SOM Included SOM',
            'value' => $som_slug
        );
    } else {
        $final_item_data[] = array(
            'key' => 'PN',
            'value' => $pn
        );
    }

    //	if( $product ){
    //		$product_max = wc_get_product_max_limit( $cart_item['product_id'] );
    //		if( $product_max ){
    //			$current_lang = apply_filters( 'wpml_current_language', NULL );
    //			$final_item_data[] = array(
    //				'key' 	=> ( $current_lang == "de" ) ? 'Maximale Einheiten' : 'Max units per order',
    //				'value' => $product_max
    //			);
    //		}
    //	}

    return $final_item_data;
}
add_filter('woocommerce_get_item_data', 'add_cart_extra_som_and_kit_info', 10, 2);

// Replace product image in cart
// For kits: fetch the first image depending on the variation chosen
function use_shop_image_for_product_in_cart($product_get_image, $cart_item, $cart_item_key)
{

    $_pf = new WC_Product_Factory();
    $_product = $_pf->get_product($cart_item['product_id']);

    $terms = get_the_terms($_product->get_id(), 'product_cat');
    $product_categories = array();

    foreach ($terms as $term) {
        $product_categories[] = $term->slug;
    }

    $product_thumbnail = get_field('product_shop_page_image', $cart_item['product_id']) ? get_field('product_shop_page_image', $cart_item['product_id'])['url'] : wp_get_attachment_image_src(get_post_thumbnail_id($cart_item['product_id']), 'single-post-thumbnail')[0];

    // If kit: get the variation first image for the kit type selected
    if (in_array('evaluation-kit', $product_categories)) {

        $selected_kit = $cart_item['variation']['attribute_pa_kit'];
        $kit_tax_obj = get_term_by('slug', $selected_kit, 'pa_kit');

        if ($kit_tax_obj && !empty($kit_tax_obj)) {
            $kit_gallery = get_field('kit_gallery', 'pa_kit_' . $kit_tax_obj->term_id);

            if ($kit_gallery && !empty($kit_gallery)) {
                $product_thumbnail = $kit_gallery[0]['url'];
            }
        }
    }

    return '<img src="' . $product_thumbnail . '" alt="' . $_product->get_name() . '" />';
}
add_filter('woocommerce_cart_item_thumbnail', 'use_shop_image_for_product_in_cart', 10, 3);

function determine_product_type($product)
{

    // Get the product's category
    if ($product->is_type('simple')) {
        $terms = get_the_terms($product->get_id(), 'product_cat');
    } else if ($product->get_parent_id() !== 0) {
        $terms = get_the_terms($product->get_parent_id(), 'product_cat');
    } else {
        $terms = get_the_terms($product->get_id(), 'product_cat');
    }

    // Determine type of product
    $is_accessory = false;

    if (!$terms) {
        $is_accessory = true;
    } else {
        foreach ($terms as $product_cat) {

            if ($product_cat->slug === 'evaluation-kit') {
                return 'kit';
            } else if ($product_cat->slug === 'system-on-module') {
                return 'som';
            } else {
                $is_accessory = true;
            }
        }
    }

    if ($is_accessory) {
        return 'accessory';
    }
}

// Redirect the user to cart after adding a product to the cart
function redirect_user_to_cart_upon_product_addition($url, $product)
{
    if ($product && is_a($product, 'WC_Product')) {
        global $sitepress;
        $current_lang = $sitepress->get_current_language();
        if ($current_lang == "de") {
            $url = '/de/cart/';
        } else {
            $url = '/cart/';
        }
    }
    return $url;
}
add_filter('woocommerce_add_to_cart_redirect', 'redirect_user_to_cart_upon_product_addition', 999, 2);

// Restrict checkout to English characters, Numbers, spaces and special characters only
add_action('woocommerce_checkout_process', 'restrict_checkout_to_english_and_digits');
function restrict_checkout_to_english_and_digits()
{

    $posted_fields = $_POST;
    $contains_non_english = false;
    $field_key_non_english = '';

    $posted_keys = array('billing_first_name', 'billing_last_name', 'billing_company', 'billing_country', 'billing_address_1', 'billing_address_2', 'billing_state', 'billing_postcode', 'billing_city');

    $shipping_fields = array('shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_country', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_state', 'shipping_postcode', 'shipping_company');

    if (isset($posted_fields['ship_to_different_address']) && $posted_fields['ship_to_different_address'] == 1) {
        $posted_keys = array_merge($posted_keys, $shipping_fields);
    }

    foreach ($posted_fields as $field_key => $posted_field) {

        $error_field = '';

        if (!in_array($field_key, $posted_keys)) {
            continue;
        }

        if (preg_match("/^[\w\d\s.,&$#@()?%!\/-]*$/", $posted_field) !== 1) {
            $error_field = $posted_field;
            $contains_non_english = true;
            $field_key_non_english = $field_key;
        }
    }

    if ($contains_non_english) {
        wc_add_notice(__($field_key_non_english . ': Only input in English is allowed.'), 'error');
    }
}

// Add class with product count to product gallery
add_filter('woocommerce_single_product_image_gallery_classes', 'add_data_counter_to_product_gallery');
function add_data_counter_to_product_gallery($classes)
{
    global $product;

    $attachment_ids = $product->get_gallery_image_ids();
    $attachment_count = count($attachment_ids);

    if (has_post_thumbnail()) {
        $attachment_count++;
    }

    $classes[] = $attachment_count === 1 ? 'single-image' : 'multi-image';
    return $classes;
}

/**
 * Change number of products that are displayed per page (shop page)
 */
add_filter('loop_shop_per_page', 'new_loop_shop_per_page', 20);

function new_loop_shop_per_page($cols)
{
    // $cols contains the current number of products per page based on the value stored on Options -> Reading
    // Return the number of products you wanna show per page.
    $cols = 15;
    return $cols;
}

/**
 * Rename "home" in breadcrumb
 */
add_filter('woocommerce_breadcrumb_defaults', 'wcc_change_breadcrumb_home_text');
function wcc_change_breadcrumb_home_text($defaults)
{
    // Change the breadcrumb home text from 'Home' to 'Apartment'
    $defaults['home'] = 'Variscite Online Store';
    return $defaults;
}

/**
 * Change the breadcrumb separator
 */
add_filter('woocommerce_breadcrumb_defaults', 'wcc_change_breadcrumb_delimiter');
function wcc_change_breadcrumb_delimiter($defaults)
{
    // Change the breadcrumb delimeter from '/' to '>'
    $defaults['delimiter'] = ' » ';
    return $defaults;
}

function variscite_hide_shipping_title($label)
{
    return str_replace(': ', '', $label);
}
add_filter('woocommerce_cart_shipping_method_full_label', 'variscite_hide_shipping_title');






function show_product_thank_you_content($product)
{
    $product_id = $product->get_id();
    $product_type = determine_product_type($product);

    $pn = $product->get_sku();

    $returned_template = '<ul class="wc-item-meta">';

    if ($product_type === 'kit') {
        $som_slug = $product->attributes['pa_som-configuration'];
        $included_som = get_term_by('slug', $som_slug, 'pa_som-configuration');

        // Operating System
        $os = $product->attributes['pa_operating-system'];
        $included_os = get_term_by('slug', $os, 'pa_operating-system');

        // SOM Part Number
        $som_pn = get_field('system_on_module_pn', 'pa_som-configuration_' . $included_som->term_id);

        $returned_template .= '<li><strong class="wc-item-meta-label">Kit PN:</strong><p>' . $pn . '</p></li>';
        $returned_template .= '<li><strong class="wc-item-meta-label">Included SOM:</strong><p>' . $included_som->name . '</p></li>';
        $returned_template .= '<li><strong class="wc-item-meta-label">SOM PN:</strong><p>' . $som_pn . '</p></li>';
        $returned_template .= '<li><strong class="wc-item-meta-label">Operating System:</strong><p>' . $included_os->name . '</p></li>';
    } else if ($product_type === 'som') {
        $som_slug = $product->attributes['pa_som-configuration'];
        $included_som = get_term_by('slug', $som_slug, 'pa_som-configuration');

        $returned_template .= '<li><p>' . $included_som->name . '</p></li>';
        $returned_template .= '<li><strong class="wc-item-meta-label">SOM PN:</strong><p>' . $pn . '</p></li>';
    } else {
        $returned_template .= '<li><strong class="wc-item-meta-label">PN:</strong><p>' . $pn . '</p></li>';
    }

    return $returned_template . '</ul>';
}

// Homepage filtering and sorting related functions
// Change URL and class of filter menu items according to the current URL
function update_url_and_class_of_filters($items, $menu)
{

    // For CPU Name or Product Type menus
    if ($menu->term_id == 70 || $menu->term_id == 72) {

        // Go over all menu items
        foreach ($items as $item) {

            $current_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $current_url = parse_url($current_url);

            $current_cpu_name = $current_product_type = '';
            if (isset($current_url['query'])) {
                parse_str($current_url['query'], $query);

                if (isset($query['cpu_name'])) {
                    $current_cpu_name = $query['cpu_name'];
                }
                if (isset($query['product_type'])) {
                    $current_product_type = $query['product_type'];
                }
            }
            // (1) Add 'checked' attribute if the title exists in the URL
            if ($menu->term_id == 70 && isset($_GET['cpu_name'])) {
                $cpu_name_arr = explode(',', trim($_GET['cpu_name']));

                foreach ($cpu_name_arr as $cpu_name) {

                    if (strpos($cpu_name, trim($item->attr_title)) !== false && strlen($cpu_name) <= strlen(trim($item->attr_title))) {
                        $item->classes[] = 'checked';
                    }
                }
            }

            if ($menu->term_id == 72 && isset($_GET['product_type'])) {
                $product_type = trim($_GET['product_type']);

                $current_object_id = $item->object_id;
                $product_category = get_term_by('id', $current_object_id, 'product_cat');

                if (strpos($product_type, $product_category->slug) !== false) {
                    $item->classes[] = 'checked';
                }
            }

            // (2) Change the URLs according to the current URL and Title attribute / Term slug
            if ($menu->term_id == 70) {

                if (isset($_GET['cpu_name'])) {

                    $has_cpu = false;
                    $cpu_name_arr = explode(',', trim($_GET['cpu_name']));

                    foreach ($cpu_name_arr as $cpu_key => $cpu_name) {

                        if (strpos($cpu_name, trim($item->attr_title)) !== false && strlen($cpu_name) <= strlen(trim($item->attr_title))) {
                            $has_cpu = true;
                            unset($cpu_name_arr[$cpu_key]);
                        }
                    }

                    if (!$has_cpu) {
                        $current_cpu_name .= ',' . $item->attr_title;
                    } else {
                        $current_cpu_name = implode(',', $cpu_name_arr);
                    }
                } else {
                    $current_cpu_name .= ',' . $item->attr_title;
                }
            } else if ($menu->term_id == 72) {

                //$product_type = $_GET['product_type'];

                $current_object_id = $item->object_id;
                $product_category = get_term_by('id', $current_object_id, 'product_cat');

                if (isset($_GET['product_type']) && strpos(trim($_GET['product_type']), $product_category->slug) !== false) {
                    $current_product_type = str_replace($product_category->slug, '', $current_product_type);
                } else {
                    $current_product_type .= ',' . $product_category->slug;
                }
            }

            $final_filter_url = get_home_url() . '/?cpu_name=' . $current_cpu_name . '&product_type=' . $current_product_type;
            parse_str(parse_url($final_filter_url)['query'], $query);

            if (empty($query['cpu_name'])) {
                $final_filter_url = str_replace('?cpu_name=', '', $final_filter_url);
                $final_filter_url = str_replace('&product_type=', '?product_type=', $final_filter_url);
            }

            if (empty($query['product_type'])) {
                $final_filter_url = str_replace('&product_type=', '', $final_filter_url);
                $final_filter_url = str_replace('?product_type=', '', $final_filter_url);
            }

            $item->url = str_replace(
                ',,',
                ',',
                str_replace('=,', '=', rtrim($final_filter_url, ','))
            );
        }
    }

    return $items;
}
add_filter('wp_get_nav_menu_items', 'update_url_and_class_of_filters', 20, 2);

// Add noindex follow links to pages with more than 1 filter type
function add_noindex_nofollow_to_filters($atts, $item, $args, $depth)
{

    if ($args->menu->term_id === 70 || $args->menu->term_id === 72) {

        $item_url = $item->url;
        $item_url = parse_url($item_url);
        if (isset($item_url['query'])) {
            parse_str($item_url['query'], $query);

            if (isset($query['cpu_name']) || isset($query['product_type'])) {
                $cpu_names = $product_types = array();
                if (isset($query['cpu_name'])) {
                    $cpu_names = explode(',', $query['cpu_name']);
                }
                if (isset($query['product_type'])) {
                    $product_types = explode(',', $query['product_type']);
                }
                if (count($cpu_names) > 1 || count($product_types) > 1) {
                    $atts['rel'] = 'follow noindex';
                }
            }
        }
    }

    return $atts;
}
add_filter('nav_menu_link_attributes', 'add_noindex_nofollow_to_filters', 10, 4);

// Add filters tray shortcode
function home_filters_tray()
{
    global $paged;
    $paged_url = '/page/' . $paged;

    $current_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $current_url = parse_url($current_url);

    $current_cpu_name = $current_product_type = '';
    if (isset($current_url['query'])) {
        parse_str($current_url['query'], $query);

        if (isset($query['cpu_name'])) {
            $current_cpu_name = $query['cpu_name'];
        }
        if (isset($query['product_type'])) {
            $current_product_type = $query['product_type'];
        }
    }
    ob_start();
?>
    <style type="text/css">
        .filter_section .filter_container {
            padding: 23px 21px;
        }

        .filter_section .filter_container .filter_item {
            padding: 10px 10px 10px 0px;
            font-size: 14px;
            cursor: pointer;
            display: flex;
            justify-content: flex-start;
            color: #fff;
            display: table-cell;
        }

        span.removeFilterCross {
            color: #fff;
            cursor: pointer;
            padding: 0;
            margin: 0;
            display: table-cell;
        }

        .filter_section .filter_container .removeFilterCross .filter_item {
            padding: 0;
            margin: 0;
            font-size: 17px;
        }
    </style>
    <div class="filter_section" <?php echo ($current_cpu_name || $current_product_type ? ' style="display: block"' : ''); ?>>
        <div class="filter_title">
            <div><?php _e('Filter by', 'variscite'); ?></div>
            <div class="clear_all"><a href="<?php echo get_home_url(); ?>"><?php _e('Clear Filters', 'variscite'); ?></a></div>
        </div>

        <div class="filter_container">
            <ul class="filters-list row">
                <?php
                $chips = array();

                if ($current_cpu_name) {
                    $product_types = '';
                    if (isset($_GET['product_type'])) {
                        $product_types = $_GET['product_type'];
                    }
                    $cpu_names     = explode(',', $current_cpu_name);
                    $cpus_in_url   = explode(',', $_GET['cpu_name']);

                    foreach ($cpu_names as $name) {
                        $new_cpus_array = $cpus_in_url;

                        foreach ($new_cpus_array as $cpu_key => $cpu_name) {

                            if ($cpu_name == $name) {
                                unset($new_cpus_array[$cpu_key]);
                            }
                        }

                        $link_without_cpu = (get_home_url() . (!empty($new_cpus_array) ? ('?cpu_name=' . implode(',', $new_cpus_array)) : ''));

                        $link_with_this_cpu = (get_home_url() . (!empty($name) ? ('?cpu_name=' . $name) : ''));

                        if ($product_types && !empty($product_types)) {
                            $link_without_cpu .= (!empty($new_cpus_array) ? ('&product_type=' . $product_types) : ('?product_type=' . $product_types));
                        }

                        $chips[] = array(
                            'name'     => get_term_by('slug', $name, 'pa_cpu-name')->name,
                            'link'     => $link_without_cpu,
                            'link_this'     => $link_with_this_cpu,
                            'nofollow' => count($new_cpus_array) > 2
                        );
                    }
                }

                if ($current_product_type) {
                    $cpu_names     = '';
                    if (isset($_GET['cpu_name'])) {
                        $cpu_names     = $_GET['cpu_name'];
                    }
                    $product_types = explode(',', $current_product_type);
                    $prods_in_url  = explode(',', $_GET['product_type']);

                    foreach ($product_types as $type) {
                        $new_products_array = $prods_in_url;

                        foreach ($new_products_array as $prod_key => $prod_name) {

                            if ($prod_name == $type) {
                                unset($new_products_array[$prod_key]);
                            }
                        }

                        $link_without_prod = (get_home_url() . (!empty($new_products_array) ? ('?product_type=' . implode(',', $new_products_array)) : ''));
                        $link_with_this_prod = (get_home_url() . (!empty($type) ? ('?product_type=' . $type) : ''));

                        if ($cpu_names && !empty($cpu_names)) {
                            $link_without_prod .= (!empty($new_products_array) ? ('&cpu_name=' . $cpu_names) : ('?cpu_name=' . $cpu_names));
                        }

                        $chips[] = array(
                            'name'     => get_term_by('slug', $type, 'product_cat')->name,
                            'link'     => $link_without_prod,
                            'link_this'     => $link_with_this_prod,
                            'nofollow' => count($new_products_array) > 2
                        );
                    }
                }
                //print_r($chips);

                if ($chips && !empty($chips)) :
                    foreach ($chips as $chip) :
                ?>
                        <li class="col-md-6 btn-filter" field-val="<?php echo $chip['name']; ?>">
                            <span class="removeFilterCross">
                                <a href="<?php echo $chip['link']; ?>" <?php echo ($chip['nofollow'] ? 'noindex follow' : ''); ?> class="filter_item">
                                    <i class="fa fa-times-circle" aria-hidden="true"></i>
                                </a>
                            </span>
                            <a href="<?php echo $chip['link_this']; ?>" class="filter_item">
                                <?php echo $chip['name']; ?>
                            </a>
                            <!-- <a href="<?php echo $chip['link']; ?>" <?php echo ($chip['nofollow'] ? 'noindex follow' : ''); ?> class="col-md-6 filter_item">
                        <i class="fa fa-times-circle" aria-hidden="true"></i>
                        <?php echo $chip['name']; ?>
                    </a> -->
                        </li>
                <?php
                    endforeach;
                endif;
                ?>
            </ul>
        </div>
    </div>

<?php
    $return_template = ob_get_contents();
    ob_end_clean();

    return $return_template;
}
add_shortcode('homepage-filters-tray', 'home_filters_tray');

// Add shop title shortcode
function home_store_title()
{
    $title = __('Variscite Online-Store', 'variscite');

    if (isset($_GET['cpu_name']) || isset($_GET['product_type'])) {
        $title = build_title_by_get_filter_params();
    }

    return $title;
}
add_shortcode('homepage-store-title', 'home_store_title');

// Update page title according to the current filter selection
function filter_product_wpseo_title($title)
{

    if (is_front_page()) {

        if (isset($_GET['cpu_name']) || isset($_GET['product_type'])) {
            $title = build_title_by_get_filter_params() . '| Variscite Online-Store';
        }
    }

    return $title;
}
add_filter('wpseo_title', 'filter_product_wpseo_title');

function build_title_by_get_filter_params()
{

    $title = '';

    if (isset($_GET['cpu_name'])) {

        $cpu_name = explode(',', $_GET['cpu_name'])[0];
        $cpu_name_name = get_term_by('slug', $cpu_name, 'pa_cpu-name')->name;

        $title .= $cpu_name_name . ', ';
    }

    if (isset($_GET['product_type'])) {

        $cpu_name = explode(',', $_GET['product_type'])[0];
        $product_type_name = get_term_by('slug', $cpu_name, 'product_cat')->name;

        $title .= $product_type_name . ' ';
    } else {
        $title = str_replace(', ', ' ', $title);
    }

    return $title;
}

// Change the canonical tag based on the current product filtering
function set_canonical_for_filtered_products($url)
{

    if (is_front_page()) {

        if (isset($_GET['cpu_name']) || isset($_GET['product_type'])) {

            $canonical = get_home_url();

            $current_url = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            parse_str(parse_url($current_url)['query'], $query);

            if ($query['cpu_name']) {
                $cpu_name = explode(',', $query['cpu_name']);

                if (count($cpu_name) == 1) {
                    $canonical .= '/?cpu_name=' . $cpu_name[0];
                }
            }

            if ($query['product_type']) {
                $product_category = explode(',', $query['product_type']);

                if (count($product_category) == 1) {
                    $canonical .= '/?product_category=' . $product_category[0];
                }
            }

            return $canonical;
        }
    }

    return $url;
}
//add_filter('wpseo_canonical', 'set_canonical_for_filtered_products');

// Add noindex follow to filter pages
function vari_add_nofollow_no_index_to_filtered_products($string = "")
{
    if (is_front_page() && (isset($_GET['cpu_name']) || isset($_GET['product_type']))) {
        $string = "noindex, nofollow";
    }
    return $string;
}
add_filter('wpseo_robots', 'vari_add_nofollow_no_index_to_filtered_products', 999);

// Filter products according to $_GET filters
function filter_products_by_get($query)
{

    if (is_front_page()) {

        $tax_query = array(
            'relation' => 'AND'
        );

        if (isset($_GET['cpu_name'])) {

            $cpu_names = array_unique(explode(',', $_GET['cpu_name']));

            $arr1 = array(
                'taxonomy' => 'pa_cpu-name',
                'field' => 'slug',
                'terms' => $cpu_names,
                'operator' => 'IN'
            );

            array_push($tax_query, $arr1);
        }

        if (isset($_GET['product_type'])) {

            $product_types = array_unique(explode(',', $_GET['product_type']));

            $arr2 = array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $product_types,
                'operator' => 'IN'
            );

            array_push($tax_query, $arr2);
        }

        $query->set('tax_query', $tax_query);

        // Add menu order
        $query->set('orderby', 'menu_order');
        $query->set('order', 'ASC');
    }
}
add_action('woocommerce_product_query', 'filter_products_by_get');

// Add tag manager code to head
function gtmanager_code_in_head()
{

    echo '<!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({\'gtm.start\':
        new Date().getTime(),event:\'gtm.js\'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!=\'dataLayer\'?\'&l=\'+l:\'\';j.async=true;j.src=
        \'https://www.googletagmanager.com/gtm.js?id=\'+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,\'script\',\'dataLayer\',\'GTM-NZ6BXV\');</script>
        <!-- End Google Tag Manager -->' . "\n" . "\n";
}
add_action('wp_head', 'gtmanager_code_in_head', 3);


// For thank you page - add transaction datalayer
function order_received_transaction_datalayer()
{
    if (is_order_received_page() && (!isset($_GET['count']) || $_GET['count'] !== 'no')) {
        global $wp;
        $order_id = absint($wp->query_vars['order-received']);
        $order = wc_get_order($order_id);
        if ($order) {
            $datalayer = "<script>" . "\n" . "dataLayer = [{" . "\n" . "'event': 'ecomm_event'," . "\n";

            $datalayer_object = array(
                'transactionId' => (string) $order->get_order_number(),
                'transactionAffiliation' => html_entity_decode(get_bloginfo('name'), ENT_QUOTES, 'utf-8'),
                'transactionTotal' => $order->get_total(),
                'transactionTax' => $order->get_total_tax(),
                'transactionShipping' => $order->get_shipping_total(),
                'transactionProducts' => array()
            );

            if ($order->get_items()) {
                $_products = [];
                $_sumprice = 0;

                foreach ($order->get_items() as $item) {

                    $product = $order->get_product_from_item($item);
                    $product_id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();
                    $product_sku = $product->get_sku();

                    $product_categories = get_the_terms($product_id, 'product_cat');

                    if ((is_array($product_categories)) && (count($product_categories) > 0)) {
                        $product_cat = array_pop($product_categories);
                        $product_cat = $product_cat->name;
                    } else {
                        $product_cat = '';
                    }

                    $productId = $product_sku ? $product_sku : $product_id;
                    $product_price = $order->get_item_total($item);

                    $product_data = [
                        'id' => (string)$productId,
                        'name' => $item['name'],
                        'sku' => $product_sku ? $product_sku : $product_id,
                        'category' => $product_cat,
                        'price' => $product_price,
                        'currency' => get_woocommerce_currency(),
                        'quantity' => $item['qty']
                    ];

                    $datalayer_object['transactionProducts'][] = $product_data;
                }
            }

            foreach ($datalayer_object as $obj_key => $obj) {

                if ($obj_key == 'transactionProducts') {

                    $datalayer .= "'" . $obj_key . "': [" . "\n";

                    foreach ($obj as $obj_prod) {

                        $datalayer .= "{" . "\n";

                        foreach ($obj_prod as $obj_prod_key => $obj_prod_item) {
                            $datalayer .= "'" . $obj_prod_key . "': '" . $obj_prod_item . "', " . "\n";
                        }

                        $datalayer .= "}," . "\n";
                    }

                    $datalayer .= "]" . "\n";
                } else {

                    if ($obj_key == 'transactionTotal' || $obj_key == 'transactionTax' || $obj_key == 'transactionShipping') {
                        $datalayer .= "'" . $obj_key . "': " . $obj . ", " . "\n";
                    } else {
                        $datalayer .= "'" . $obj_key . "': '" . $obj . "', " . "\n";
                    }
                }
            }

            $datalayer .= '}];' . "\n" . '</script>' . "\n";
            echo $datalayer;
        }
    }
}
add_action('wp_head', 'order_received_transaction_datalayer', 2);

// Accessory product - add schema to top of page
// function add_schema_for_simple_product()
// {

//     if (is_product()) {

//         $_pf = new WC_Product_Factory();
//         $_product = $_pf->get_product(get_the_ID());

//         if ($_product->is_type('simple')) {

//             $schema = '<script type="application/ld+json">';

//             $schema_array = json_encode(array(
//                 '@context' => 'http://schema.org/',
//                 '@type' => 'Product',
//                 'name' => $_product->get_name(),
//                 'image' => array(
//                     get_the_post_thumbnail_url($_product->get_id(), 'full')
//                 ),
//                 'description' => $_product->get_short_description(),
//                 'mpn' => $_product->get_sku(),
//                 'brand' => array(
//                     '@type' => 'Thing',
//                     'name' => 'Variscite'
//                 ),
//                 'offers' => array(
//                     '@type' => 'Offer',
//                     'priceCurrency' => 'USD',
//                     'price' => $_product->get_price(),
//                     'priceValidUntil' => '2020-11-05',
//                     'itemCondition' => 'http://schema.org/UsedCondition',
//                     'availability' => 'http://schema.org/InStock',
//                     'seller' => array(
//                         '@type' => 'Organization',
//                         'name' => 'Variscite'
//                     )
//                 )
//             ));

//             $schema .= $schema_array . '</script>';
//             echo $schema . "\n" . "\n";
//         }
//     }
// }
// add_action('wp_head', 'add_schema_for_simple_product');



add_filter('woocommerce_structured_data_product', 'add_brand_to_product_schema', 10, 2);

function add_brand_to_product_schema($markup, $product) {
    // Add brand information to the product schema
    $markup['brand'] = array(
        '@type' => 'Brand',
        'name' => 'Variscite'
    );
    return $markup;
}


// Contact page: send data to Pardot on CF7 submission
function contact_us_pass_pardot_data()
{

    // Get current form & submission and it's data
    $wpcf = WPCF7_ContactForm::get_current();
    $submission = WPCF7_Submission::get_instance();

    if ($submission) {
        $posted_data = $submission->get_posted_data();

        $sfdc_url = 'https://webto.salesforce.com/servlet/servlet.WebToLead';
        $sfdc_fields = array(
            'oid' => '00D24000000I9Kc',
            'lead_source' => 'Web - contact via store',
            'first_name' => htmlspecialchars($posted_data['first-name']),
            'last_name' => htmlspecialchars($posted_data['last-name']),
            'company' => htmlspecialchars($posted_data['company-name']),
            'email' => htmlspecialchars($posted_data['email']),
            'phone' => htmlspecialchars($posted_data['phone']),
            'country' => htmlspecialchars($posted_data['country-name']),
            '00N24000004Cp7X' => htmlspecialchars($posted_data['message']) // Note__c
        );

        // Privacy policy
        if (!empty($posted_data['privacy-policy'][0]) && isset($posted_data['privacy-policy'][0])) {
            $sfdc_fields['00N1p00000JVK3Y'] = htmlspecialchars($posted_data['privacy-policy'][0]);
        }

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        curl_setopt($ch, CURLOPT_URL, $sfdc_url);
        curl_setopt($ch, CURLOPT_POST, count($sfdc_fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($sfdc_fields));

        $result = curl_exec($ch);

        curl_close($ch);
	
    }
}
//add_action('wpcf7_before_send_mail', 'contact_us_pass_pardot_data');

// Add error span to all fields that require validation on checkout
function variscite_checkout_fields_in_label_error($field, $key, $args, $value)
{

    if (strpos($field, '</label>') !== false) {
        $error = '<span class="error" style="display:none"></span>';
        $field = substr_replace($field, $error, strpos($field, '</label>'), 0);
    }

    return $field;
}
add_filter('woocommerce_form_field', 'variscite_checkout_fields_in_label_error', 10, 4);

// Custom JS validation for checkout form
function variscite_inline_js_checkout_validation()
{
    if (!is_checkout()) return;
?>

    <script>
        // Set limit on the address field
        // jQuery('#shipping_address_1, #billing_address_1, #shipping_address_2, #billing_address_2').attr('maxlength', '80');

        jQuery(document).ready(function() {
            jQuery('.form-row').find('input, select').trigger('change');

            setTimeout(function() {
                jQuery('.form-row').find('input, select').trigger('change');
            }, 1000);
        });

        jQuery(window).on('load pageshow', function() {
            jQuery('.form-row').find('input, select').trigger('change');

            setTimeout(function() {
                jQuery('.form-row').find('input, select').trigger('change');
            }, 1000);
        });

        // Disable paste events on the confirmation fields
        jQuery(document).on('paste', '#billing_email_addr_confirmation, #shipping_email_addr_confirmation', function(e) {
            e.preventDefault();
        });

        // Run a custom front-end validation before WooCommerce submits the data to the backend
        jQuery('input[name="woocommerce_checkout_place_order"]').on('click', function(e) {
            e.preventDefault();

            var checkout_is_valid = true,
                row_input;

            jQuery('.form-row').each(function() {

                // Remove all error notes from inputs
                jQuery(this).removeClass('variscite-invalid').find('.error-note').remove();

                if (jQuery(this).is(':visible')) {

                    if (jQuery(this).find('input').length) {

                        // Check if checkbox is checked for terms and conditions field
                        if (jQuery(this).parents('.submit-and-terms').length) {
                            row_input = jQuery(this).find('input[type="checkbox"]');
                        } else {
                            row_input = jQuery(this).find('input');
                        }

                    } else {
                        row_input = jQuery(this).find('select');
                    }

                    // Validate all required fields are filled
                    if (jQuery(this).hasClass('validate-required') || (jQuery(this).hasClass('validate-state') && jQuery(this).find('select') && jQuery(this).find('select').length > 0)) {

                        if (
                            ((!row_input.val() || row_input.val().length <= 0) && !row_input.is(':checkbox')) ||
                            (row_input.is(':checkbox') && !row_input.is(':checked'))
                        ) {
                            jQuery(this).addClass('variscite-invalid').append('<span class="error-note"><?php _e('This field is required', 'variscite') ?></span>');
                            checkout_is_valid = false;
                        }
                    }

                    // Validate English content
                    if (
                        row_input.attr('type') != 'tel' &&
                        !validateEnglish(row_input.val()) &&
                        !row_input.is(':checkbox') &&
                        !jQuery(this).hasClass('variscite-invalid') &&
                        !jQuery(this).hasClass('is-visually-hidden')
                    ) {
                        jQuery(this).addClass('variscite-invalid').append('<span class="error-note"><?php _e('Only English input is allowed', 'var-checkout') ?></span>');
                        checkout_is_valid = false;
                    }

                    // Validate first and last names
                    if (
                        (
                            row_input.attr('name') == 'billing_first_name' ||
                            row_input.attr('name') == 'billing_last_name' ||
                            row_input.attr('name') == 'shipping_first_name' ||
                            row_input.attr('name') == 'shipping_last_name'
                        ) &&
                        !validateOnlyEnglish(row_input.val()) &&
                        !jQuery(this).hasClass('variscite-invalid') &&
                        !jQuery(this).hasClass('is-visually-hidden')
                    ) {
                        if (window.location.href.indexOf(".de") > -1) {
                            var field_name = row_input.parents('.form-row').find('> label').text().replace('*', '').toLowerCase().trim();
                            jQuery(this).addClass('variscite-invalid').append('<span class="error-note">Bitte geben Sie einen gültigen ' + field_name + 'n ein</span>');
                            checkout_is_valid = false;
                        } else {
                            var field_name = row_input.parents('.form-row').find('> label').text().replace('*', '').toLowerCase();
                            jQuery(this).addClass('variscite-invalid').append('<span class="error-note">Please enter a valid ' + field_name + '</span>');
                            checkout_is_valid = false;
                        }
                    }

                    // Validate Email
                    if (row_input.attr('type') == 'email' && !validateEmail(row_input.val()) && !jQuery(this).hasClass('variscite-invalid')) {
                        jQuery(this).addClass('variscite-invalid').append('<span class="error-note"><?php _e('Please enter a valid email address', 'var-checkout') ?></span>');
                        checkout_is_valid = false;
                    }

                    // Validate phone
                    //  if(row_input.attr('type') == 'tel' && ! validatePhone(row_input.val()) && ! jQuery(this).hasClass('variscite-invalid')) {
                    //      jQuery(this).addClass('variscite-invalid').append('<span class="error-note">Please enter a valid phone number</span>');
                    //      checkout_is_valid = false;
                    //  }

                    if (row_input.attr('type') == 'tel' && !jQuery(this).hasClass('variscite-invalid')) {
                        var input_wrapper = jQuery(this);

                        jQuery.ajax({
                            type: 'POST',
                            url: '/wp-admin/admin-ajax.php',
                            async: false,
                            data: {
                                action: 'validate_phone_checkout',
                                phone: row_input.val()
                            },
                            success: function(data) {

                                if (jQuery.parseJSON(data).is_valid == false) {
                                    input_wrapper.addClass('variscite-invalid').append('<span class="error-note"><?php _e('Please enter a valid phone number', 'var-checkout') ?></span>');
                                    checkout_is_valid = false;
                                }
                            }
                        });
                    }

                    // Validate phone length
                    if (row_input.attr('type') == 'tel' && row_input.val().length > 20 && !jQuery(this).hasClass('variscite-invalid')) {
                        jQuery(this).addClass('variscite-invalid').append('<span class="error-note"><?php _e('The phone number must be under 20 characters.', 'var-checkout') ?></span>');
                        checkout_is_valid = false;
                    }

                    // Validate the phone field
                    if (row_input.attr('type') == 'tel' && !validatePhone(row_input.val()) && !jQuery(this).hasClass('variscite-invalid')) {
                        jQuery(this).addClass('variscite-invalid').append('<span class="error-note"><?php _e('Please enter a valid phone number', 'var-checkout') ?></span>');
                        checkout_is_valid = false;
                    }

                    // Validate the company reg number field
                    if (row_input.attr('name') == 'billing_company_reg_number' && !jQuery(this).hasClass('variscite-invalid')) {
                        var country = jQuery('#billing_country').val();

                        if (country == 'IL' && row_input.val().length <= 0) {
                            jQuery(this).addClass('variscite-invalid').append('<span class="error-note"><?php _e("Please enter a valid company registration number", "var-checkout"); ?></span>');
                            checkout_is_valid = false;
                        }
                    }

                    // Validate postcode
                    if (jQuery(this).hasClass('validate-postcode')) {

                        var country = '',
                            input_wrapper = jQuery(this);

                        if (jQuery(this).parents('.woocommerce-billing-fields').length) {
                            country = jQuery('#billing_country').val();
                        } else {
                            country = jQuery('#shipping_country').val();
                        }

                        jQuery.ajax({
                            type: 'POST',
                            url: '/wp-admin/admin-ajax.php',
                            async: false,
                            data: {
                                action: 'validate_zip_code_checkout',
                                country: country,
                                zip: row_input.val()
                            },
                            success: function(data) {

                                if (jQuery.parseJSON(data).is_valid == false) {
                                    input_wrapper.addClass('variscite-invalid').append('<span class="error-note"><?php _e("Please enter a valid ZIP code", "var-checkout"); ?></span>');
                                    checkout_is_valid = false;
                                }
                            }
                        });
                    }

                    // Max limit on both address fields
                    if (
                        (row_input.attr('name') == 'billing_address_1' || row_input.attr('name') == 'billing_address_2' || row_input.attr('name') == 'shipping_address_1' || row_input.attr('name') == 'shipping_address_2') &&
                        row_input.val().length > 80
                    ) {
                        jQuery(this).addClass('variscite-invalid').append('<span class="error-note"><?php _e('The address cannot be longer than 80 characters.', 'var-checkout') ?></span>');
                        checkout_is_valid = false;
                    }

                    // Max limit on the city field
                    if (
                        (row_input.attr('name') == 'billing_city' || (row_input.attr('name') == 'shipping_city') && jQuery('input[name="ship_to_different_address"]').is(':checked')) &&
                        row_input.val().length > 50
                    ) {
                        jQuery(this).addClass('variscite-invalid').append('<span class="error-note"><?php _e('The city name cannot be longer than 50 characters.', 'var-checkout') ?></span>');
                        checkout_is_valid = false;
                    }
                }
            });

            // Validate email confirmation - billing
            var bill_email = jQuery('#billing_email'),
                bill_email_confirmation = jQuery('#billing_email_addr_confirmation');

            if (bill_email.val() !== bill_email_confirmation.val() && !bill_email.parents('.form-row').hasClass('variscite-invalid')) {
                bill_email.parents('.form-row').addClass('variscite-invalid').append('<span class="error-note">Please confirm that the email addresses are matching.</span>');
                bill_email_confirmation.parents('.form-row').addClass('variscite-invalid');

                checkout_is_valid = false;
            }

            // Validate email confirmation - shipping
            var shipp_email = jQuery('#shipping_email'),
                shipp_email_confirmation = jQuery('#shipping_email_addr_confirmation');

            if (jQuery('#ship-to-different-address-checkbox').is(':checked')) {

                if (shipp_email.val() !== shipp_email_confirmation.val() && !shipp_email.parents('.form-row').hasClass('variscite-invalid')) {
                    shipp_email.parents('.form-row').addClass('variscite-invalid').append('<span class="error-note">Please confirm that the email addresses are matching.</span>');
                    shipp_email_confirmation.parents('.form-row').addClass('variscite-invalid');

                    checkout_is_valid = false;
                }
            }


            // Validate the company registration number field
            var billing_reg_number = jQuery('#billing_company_reg_number'),
                reg_number_regex = /(^$)|(^\d{9}$)/g;

            if (!reg_number_regex.test(billing_reg_number.val()) && !billing_reg_number.parents('.form-row').hasClass('variscite-invalid')) {
                billing_reg_number.parents('.form-row').addClass('variscite-invalid').append('<span class="error-note">Please enter 9 digits only.</span>');
                checkout_is_valid = false;
            }

            if (checkout_is_valid) {
                jQuery(this).parents('form').submit();
            } else {
                jQuery('body, html').animate({
                    scrollTop: jQuery('.variscite-invalid').first().offset().top - jQuery('#site-navigation').outerHeight() - 40
                });
            }
        });

        function validateEmail(value) {
            var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
            return re.test(String(value).toLowerCase());
        }

        function validateOnlyEnglish(value) {
            var re = /^[-a-zA-Z_ ]+$/;
            return re.test(String(value));
        }

        function validateEnglish(value) {
            var re = /^[\w\d\s.,&$#@()?%!\/-]*$/;
            return re.test(String(value));
        }

        function validatePhone(value) {
            var re = /[\s\#0-9_\-\+\/\(\)\.]/;
            return re.test(String(value));
        }

        // Prevent WooCommerce default scrolling to error
        jQuery(document).ajaxComplete(function() {
            jQuery('html, body').stop();
        });
    </script>

    <?php
}
add_action('wp_footer', 'variscite_inline_js_checkout_validation');

// AJAX: validate zip code
function validate_zip_code_checkout()
{
    $is_valid = WC_Validation::is_postcode(esc_html($_POST['zip']), esc_html($_POST['country']));
    die(json_encode(array('is_valid' => $is_valid)));
}
add_action('wp_ajax_validate_zip_code_checkout', 'validate_zip_code_checkout');
add_action('wp_ajax_nopriv_validate_zip_code_checkout', 'validate_zip_code_checkout');

// AJAX: validate phone
function validate_phone_checkout()
{
    $is_valid = WC_Validation::is_phone(esc_html($_POST['phone']));
    if (strlen($_POST['phone']) > 20) {
        $is_valid = false;
    }
    die(json_encode(array('is_valid' => $is_valid)));
}
add_action('wp_ajax_validate_phone_checkout', 'validate_phone_checkout');
add_action('wp_ajax_nopriv_validate_phone_checkout', 'validate_phone_checkout');

// Add shipping phone and email fields to checkout
function add_shipping_phone_and_email_fields($fields)
{

    $fields['shipping_email'] = array(
        'label'         => 'Email',
        'required'         => true,
        'class'         => array('form-row-first'),
        'validate'        => array('email'),
    );

    $fields['shipping_phone'] = array(
        'label'     => 'Phone',
        'required'     => true,
        'type'        => 'tel',
        'class'        => array('form-row-last'),
        'clear'        => true,
        'validate'     => array('phone'),
    );

    $fields['shipping_company'] = array(
        'label'     => 'Company name / Personal name for receipt',
        'required'     => false,
        'type'        => 'text',
        'class'        => array('form-row-wide'),
        'clear'        => true,
        'validate'     => array()
    );

    return $fields;
}
add_filter('woocommerce_shipping_fields', 'add_shipping_phone_and_email_fields');

// Display shipping email and phone in the admin
function display_shipping_phone_and_email_fields_in_admin($fields)
{

    $fields['email'] = array(
        'label'         => 'Email'
    );

    $fields['phone'] = array(
        'label'         => 'Phone',
        'wrapper_class' => '_shipping_state_field'
    );

    $fields['company'] = array(
        'label'         => 'Company',
        'wrapper_class' => '_shipping_state_field'
    );

    return $fields;
}
add_filter('woocommerce_admin_shipping_fields', 'display_shipping_phone_and_email_fields_in_admin');

// display meta key in order overview
function display_shipping_phone_and_email_fields_in_order($fields, $order)
{

    $fields['shipping_email'] = get_post_meta($order->get_id(), '_shipping_email', true);
    $fields['shipping_phone'] = get_post_meta($order->get_id(), '_shipping_phone', true);
    $fields['shipping_company'] = get_post_meta($order->get_id(), '_shipping_company', true);

    return $fields;
}
add_action('woocommerce_order_formatted_shipping_address', 'display_shipping_phone_and_email_fields_in_order', 10, 2);

// modify the address formats
function format_shipping_phone_and_email_fields_in_order($formats)
{

    foreach ($formats as $key => &$format) {

        $format = str_replace("{company}", "{company}\n{shipping_phone}", $format);
        $format = str_replace("{company}", "{company}\n{shipping_email}", $format);
        $format = str_replace("{company_reg_number}", "{company}\n{company_reg_number}", $format);
    }

    return $formats;
}
add_filter('woocommerce_localisation_address_formats', 'format_shipping_phone_and_email_fields_in_order');

add_filter('woocommerce_formatted_address_replacements', 'fill_shipping_phone_and_email_formats_in_order', 10, 2);
function fill_shipping_phone_and_email_formats_in_order($replacements, $args)
{
    if (isset($args['shipping_phone']))
        $replacements['{shipping_phone}'] = $args['shipping_phone'];

    if (isset($args['shipping_email']))
        $replacements['{shipping_email}'] = $args['shipping_email'];

    if (isset($args['phone']) && !isset($args['shipping_phone']))
        $replacements['{shipping_phone}'] = $args['phone'];

    if (isset($args['email']) && !isset($args['shipping_email']))
        $replacements['{shipping_email}'] = $args['email'];

    if (isset($args['shipping_company']))
        $replacements['{shipping_company}'] = $args['shipping_company'];

    if (isset($args['company_reg_number']))
        $replacements['{company_reg_number}'] = $args['company_reg_number'];

    return $replacements;
};

// Set required fields in checkout
function set_default_fields_as_required_on_checkout($fields)
{

    $mandatory_fields = array(
        'billing' => array(
            'billing_first_name', 'billing_last_name', 'billing_country', 'billing_address_1', 'billing_city', 'billing_postcode', 'billing_phone', 'billing_email', 'billing_company'
        ),
        'shipping' => array(
            'shipping_first_name', 'shipping_last_name', 'shipping_country', 'shipping_address_1', 'shipping_city', 'shipping_postcode', 'shipping_email', 'shipping_phone', 'shipping_company'
        )
    );

    foreach ($fields['billing'] as $field_key => $billing_field) {

        if (in_array($field_key, $mandatory_fields['billing'])) {
            $fields['billing'][$field_key]['required'] = true;
        } else {
            $fields['billing'][$field_key]['required'] = false;
        }
    }

    foreach ($fields['shipping'] as $field_key => $billing_field) {

        if (in_array($field_key, $mandatory_fields['shipping'])) {
            $fields['shipping'][$field_key]['required'] = true;
        } else {
            $fields['shipping'][$field_key]['required'] = false;
        }
    }

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'set_default_fields_as_required_on_checkout', 10, 1);

// Make shipping and billing states required depending on country
function make_state_required_on_countries_with_state($address_fields)
{
    $wc = WC();
    $country = $wc->customer->get_billing_country();

    if ($country !== 'US') {
        $address_fields['state']['required'] = false;
    }

    return $address_fields;
}
add_filter('woocommerce_default_address_fields', 'make_state_required_on_countries_with_state');

// Add the חפ and email confirmation field
function variscite_email_confirm_and_company_no_fields_billing()
{

    // Company number field
    woocommerce_form_field('billing_company_reg_number', array(
        'type'          => 'number',
        'class'         => array('company_reg_number form-row-wide'),
        'label'         => __('Company registration number', 'variscite'),
        'placeholder'   => '',
        'required'      => false,
        'default'       => '',
    ));

    // Company number field
    woocommerce_form_field('billing_email_addr_confirmation', array(
        'type'          => 'text',
        'class'         => array('email_addr_confirmation form-row-wide'),
        'label'         => __('Confirm email address', 'variscite'),
        'placeholder'   => '',
        'required'      => false,
        'default'       => '',
    ));
}
add_action('woocommerce_after_checkout_billing_form', 'variscite_email_confirm_and_company_no_fields_billing');

function variscite_email_confirm_and_company_no_fields_shipping()
{

    // Company number field
    woocommerce_form_field('shipping_email_addr_confirmation', array(
        'type'          => 'text',
        'class'         => array('email_addr_confirmation form-row-wide'),
        'label'         => __('Confirm email address', 'variscite'),
        'placeholder'   => '',
        'required'      => false,
        'default'       => ''
    ));
}
add_action('woocommerce_after_checkout_shipping_form', 'variscite_email_confirm_and_company_no_fields_shipping');

// Add the company reg number to the admin
function display_billing_company_reg_number_in_admin($fields)
{

    $fields['company_reg_number'] = array(
        'label'         => 'Company registration number',
        'wrapper_class' => '_billing_reg_number_field'
    );

    return $fields;
}
add_filter('woocommerce_admin_shipping_fields', 'display_billing_company_reg_number_in_admin');

// Save the field on checkout
function company_reg_number_field_save($order_id, $posted)
{

    if (isset($_POST['billing_company_reg_number'])) {
        update_post_meta($order_id, '_billing_company_reg_number', $_POST['billing_company_reg_number']);
    }
}
add_action('woocommerce_checkout_update_order_meta', 'company_reg_number_field_save', 10, 2);

// Display meta key in order review
function display_company_reg_billing_field_in_order($fields, $order)
{
    $fields['billing_company_reg_number'] = get_post_meta($order->get_id(), '_billing_company_reg_number', true);
    return $fields;
}
add_action('woocommerce_order_formatted_billing_address', 'display_company_reg_billing_field_in_order', 10, 2);



// Re-order checkout fields
function reorder_checkout_form_fields($fields)
{

    // Re-order billing fields
    $billing_fields_order = array(
        'billing_first_name', 'billing_last_name', 'billing_company', 'billing_email', 'billing_email_addr_confirmation', 'billing_phone', 'billing_country', 'billing_company_reg_number', 'billing_address_1', 'billing_address_2', 'billing_state', 'billing_postcode', 'billing_city', 'billing_customer_note', 'Campaign_medium__c', 'Campaign_source__c', 'Campaign_term__c', 'Page_url__c', 'Paid_Campaign_Name__c', 'curl', 'Campaign_content__c', 'GA_id__c'
    );

    $billing_fields_ordered = array();

    foreach ($billing_fields_order as $order => $field_name) {

        if (isset($fields['billing'][$field_name])) {

            if ($field_name == 'billing_customer_note') {
                $fields['billing'][$field_name]['priority'] = 9999;
            } else {
                $fields['billing'][$field_name]['priority'] = $order + 1;
            }

            $billing_fields_ordered[$field_name] = $fields['billing'][$field_name];
        }
    }

    $fields['billing'] = $billing_fields_ordered;

    // Re-order shipping fields
    $shipping_fields_order = array(
        'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_email', 'email_addr_confirmation', 'shipping_phone', 'shipping_country', 'company_reg_number', 'shipping_address_1', 'shipping_address_2', 'shipping_state', 'shipping_postcode', 'shipping_city'
    );

    $shipping_fields_ordered = array();

    foreach ($shipping_fields_order as $order => $field_name) {

        if (isset($fields['shipping'][$field_name])) {
            $fields['shipping'][$field_name]['priority'] = $order + 1;
            $shipping_fields_ordered[$field_name] = $fields['shipping'][$field_name];
        }
    }

    $fields['shipping'] = $shipping_fields_ordered;

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'reorder_checkout_form_fields', 15, 1);

// Re-order default checkout address fields
function reorder_default_checkout_form_fields($fields)
{

    $address_fields_order = array(
        'first_name', 'last_name', 'company', 'email', 'email_addr_confirmation', 'phone', 'country', 'company_reg_number', 'address_1', 'address_2', 'state', 'postcode', 'city', 'customer_note', 'Campaign_medium__c', 'Campaign_source__c', 'Campaign_term__c', 'Page_url__c', 'Paid_Campaign_Name__c', 'curl', 'Campaign_content__c', 'GA_id__c'
    );

    $address_fields_ordered = array();

    foreach ($address_fields_order as $order => $field_name) {

        if (isset($fields[$field_name])) {
            $fields[$field_name]['priority'] = $order + 1;
            $address_fields_ordered[$field_name] = $fields[$field_name];
        }
    }

    return $address_fields_ordered;
}
add_filter('woocommerce_default_address_fields', 'reorder_default_checkout_form_fields');

// Change checkout field labels
function replace_checkout_fields_label($fields)
{

    // Billing fields
    $billing_field_labels = array(
        'billing_first_name' => __('First Name', 'variscite'),
        'billing_last_name' => __('Last Name', 'variscite'),
        'billing_company' => __('Company name / Personal name for receipt', 'variscite'),
        'billing_email' => __('Email', 'variscite'),
        'billing_phone' => __('Phone', 'variscite'),
        'billing_country' => __('Country', 'variscite'),
        'billing_address_1' => __('Address', 'variscite'),
        'billing_address_2' => __('Address 2', 'variscite'),
        'billing_state' => __('State', 'variscite'),
        'billing_postcode' => __('ZIP Code', 'variscite'),
        'billing_city' => __('City', 'variscite'),
        'billing_customer_note' => __('Order Notes', 'variscite')
    );

    foreach ($fields['billing'] as $billing_field_name => $billing_field) {
        $fields['billing'][$billing_field_name]['label'] = $billing_field_labels[$billing_field_name];
    }

    // Shipping fields
    $shipping_field_labels = array(
        'shipping_first_name' =>  __('First Name', 'variscite'),
        'shipping_last_name' => __('Last Name', 'variscite'),
        'shipping_email' => __('Email', 'variscite'),
        'shipping_phone' => __('Phone', 'variscite'),
        'shipping_country' => __('Country', 'variscite'),
        'shipping_address_1' => __('Address', 'variscite'),
        'shipping_address_2' => __('Address 2', 'variscite'),
        'shipping_state' => __('State', 'variscite'),
        'shipping_postcode' => __('ZIP Code', 'variscite'),
        'shipping_city' => __('City', 'variscite'),
        'shipping_company' => __('Company name / Personal name for receipt', 'variscite')
    );

    foreach ($fields['shipping'] as $shipping_field_name => $shipping_field) {
        $fields['shipping'][$shipping_field_name]['label'] = $shipping_field_labels[$shipping_field_name];
    }

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'replace_checkout_fields_label', 15, 1);

function replace_default_checkout_fields_label($fields)
{

    $field_labels = array(
        'first_name' => __('First Name', 'variscite'),
        'last_name' => __('Last Name', 'variscite'),
        'country' => __('Country', 'variscite'),
        'address_1' => __('Address', 'variscite'),
        'address_2' => __('Address 2', 'variscite'),
        'state' => __('State', 'variscite'),
        'postcode' =>  __('ZIP Code', 'variscite'),
        'city' => __('City', 'variscite')
    );

    foreach ($fields as $billing_field_name => $billing_field) {
        if (isset($field_labels[$billing_field_name])) {
            $fields[$billing_field_name]['label'] = $field_labels[$billing_field_name];
        }
    }

    return $fields;
}
add_filter('woocommerce_default_address_fields', 'replace_default_checkout_fields_label');



// Set WooCommerce expiration time for cart
add_filter('wc_session_expiring', 'extend_woo_cart_session_expiry');
add_filter('wc_session_expiration', 'extend_woo_cart_session_expiration');

function extend_woo_cart_session_expiry($seconds)
{
    return 60 * 60 * 24 * 7;
}

function extend_woo_cart_session_expiration($seconds)
{
    return 60 * 60 * 24 * 7;
}

// Accessory accordion description
function get_accessory_accordion_description($product_id)
{

    $accordion_desc_acf = get_field('variscite_accessory_accordion_description', $product_id);

    if ($accordion_desc_acf && !empty($accordion_desc_acf)) {
        return $accordion_desc_acf;
    }

    return get_the_excerpt($product_id);
}

// Replace summary with content for product page
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);
add_action('woocommerce_single_product_summary', 'the_content', 20);

// Related products - change to Cross-sells
remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);

function woocommerce_output_related_products_as_cross_sells()
{
    global $product;

    if (!$product || get_field('variscite__product_is_not_purchasable')) {
        return;
    }

    if ($upsell_ids = $product->get_cross_sell_ids()) {

        $args = apply_filters('woocommerce_upsell_display_args', array(
            'posts_per_page' => 4,
            'orderby'        => 'rand',
            'order'          => 'desc',
            'columns'        => 4
        ));

        $args['related_products'] = wc_products_array_orderby(array_filter(array_map('wc_get_product', $upsell_ids), 'wc_products_array_filter_visible'), 'rand', 'desc');

        wc_set_loop_prop('name', 'related');
        wc_set_loop_prop('columns', apply_filters('woocommerce_related_products_columns', '4'));

        wc_get_template('single-product/related.php', $args);
    }
}
add_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products_as_cross_sells', 25);

// Fetch countries list for contact form from WOO-SFDC ACF group
function contact_form_fill_from_acf()
{

    $countries_from_SFDC_list = get_field('woo_to_sfdc_countries', 'option');
    $countries_from_SFDC = explode("\n", $countries_from_SFDC_list);

    echo '<option value="">Country</option>';

    foreach ($countries_from_SFDC as $country) {
        $country_name = explode(' : ', $country);
        echo '<option value="' . $country_name[1] . '">' . $country_name[1] . '</option>';
    }
}
add_action('wp_ajax_contact_form_fill_from_acf', 'contact_form_fill_from_acf');
add_action('wp_ajax_nopriv_contact_form_fill_from_acf', 'contact_form_fill_from_acf');

// Add global settings ACF options page
if (function_exists('acf_add_options_page')) {

    acf_add_options_page(array(
        'page_title'    => 'Variscite Settings',
        'menu_title'    => 'Variscite Settings',
        'menu_slug'     => 'variscite-settings',
        'capability'    => 'edit_posts',
        'redirect'      => false,
        'icon_url'      => 'dashicons-edit',
        'position'      => 3
    ));
}

//Kit, Som - adding price before product summary - FOR Mobile
add_action('woocommerce_before_single_product_summary', 'woocommerce_template_single_price', 10);

//Kit, Som - adding title before product summary - FOR Mobile
//add_action( 'woocommerce_before_single_product_summary', 'woocommerce_template_single_title', 5 );

// Remove cross-sells from cart
remove_action('woocommerce_cart_collaterals', 'woocommerce_cross_sell_display');

// Change admin new order email subject
function change_admin_email_subject($subject, $order)
{
    global $woocommerce;

    $store_name = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
    $order_date = wc_format_datetime($order->get_date_created());

    $subject = sprintf('[%s] %s, Order (%s) - %s', $store_name, $order->get_billing_company(), $order->get_order_number(), $order_date);

    return $subject;
}
add_filter('woocommerce_email_subject_new_order', 'change_admin_email_subject', 1, 2);

// Remove thanks for reading, etc. notes from all emails
function translate_woocommerce_strings_emails_empty($translated)
{
    $translated = str_ireplace('Thanks for shopping with us.', '', $translated);
    $translated = str_ireplace('We hope to see you again soon.', '', $translated);
    $translated = str_ireplace('Thanks for reading.', '', $translated);

    return $translated;
}
add_filter('gettext', 'translate_woocommerce_strings_emails_empty', 999);

// Disable auto plugin update
add_filter('auto_update_plugin', '__return_false');

// Remove product image zoom on hover
function remove_image_zoom_support()
{
    remove_theme_support('wc-product-gallery-zoom');
}
add_action('wp', 'remove_image_zoom_support', 100);

// Remove breadcrumbs from shop page
function remove_shop_page_breadcrumbs()
{

    if (is_shop() || is_product_category()) {
        remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0);
    }
}
add_action('template_redirect', 'remove_shop_page_breadcrumbs');

function yoast_remove_shop_page_breadcrumbs($links)
{
    if (is_shop() || is_product_category()) {
        return array();
    }

    return $links;
}
add_filter('wpseo_breadcrumb_links', 'yoast_remove_shop_page_breadcrumbs');


// add missing itemListElement to schema breadcrumbs //
function replace_domain_name_to_breadcrumb_schema($piece)
{
    if (is_front_page()) {
        $piece['itemListElement'] = ["@type" => "ListItem", "position" => 1, "name" => "Variscite Online Store"];
    }
    return $piece;
}
add_filter('wpseo_schema_breadcrumb', 'replace_domain_name_to_breadcrumb_schema', 11, 2);


// Update canonical tag on product category archives
function set_canonical_for_product_category($url)
{

    if (is_product_category()) {
        $cat_slug = get_queried_object()->slug;
        return get_home_url() . '/?product_type=' . $cat_slug;
    }

    return $url;
}
add_filter('wpseo_canonical', 'set_canonical_for_product_category');

add_action('after_setup_theme', function () {
    remove_action('generate_before_header', 'generate_do_skip_to_content_link', 2);
}, 50);

// Get the total quantity of the product available in the cart.
function wc_qty_get_cart_qty($product_id, $cart_item_key = '')
{
    // Loop through cart items
    foreach (WC()->cart->get_cart() as $cart_item) {
        if (in_array($product_id, array($cart_item['product_id'], $cart_item['variation_id']))) {
            $quantity =  $cart_item['quantity'];
            break; // stop the loop if product is found
        }
    }
    return $quantity;

    //    global $woocommerce;
    //    $running_qty = 0; // iniializing quantity to 0
    //    // search the cart for the product in and calculate quantity.
    //    foreach($woocommerce->cart->get_cart() as $other_cart_item_keys => $values ) {
    //        if ( $product_id == $values['product_id'] ) {
    //            if ( $cart_item_key == $other_cart_item_keys ) {
    //                continue;
    //            }
    //            $running_qty += (int) $values['quantity'];
    //        }
    //    }
    //    return $running_qty;
}

// On add to cart: validate that the cart doesn't contain the maximum per-purchase quantity of that product (@AsafA)
function validate_maximum_quantity_on_add_to_cart($passed, $product_id, $quantity, $variation_id = '', $variations = '')
{

    $product_min = wc_get_product_min_limit($product_id);
    $product_max = wc_get_product_max_limit($product_id);


    if ($variation_id) {
        $get_product_som_special_offer_max = get_product_som_special_offer_max($variation_id, $product_id);
        if ($get_product_som_special_offer_max !== false) {
            $product_max = $get_product_som_special_offer_max;
            $product_min = $product_max;
        }
    }

    if (!empty($product_min)) {
        // min is empty
        if (false !== $product_min) {
            $new_min = $product_min;
        } else {
            // neither max is set, so get out
            return $passed;
        }
    }

    if (!empty($product_max)) {
        // min is empty
        if (false !== $product_max) {
            $new_max = $product_max;
        } else {
            // neither max is set, so get out
            return $passed;
        }
    }


    $already_in_cart = 0;
    foreach (WC()->cart->get_cart() as $cart_item) {
        if ($variation_id) {
            if ($variation_id == $cart_item['variation_id']) {
                $already_in_cart = $cart_item['quantity'];
                break; // stop the loop if product is found
            }
        } else {
            if ($product_id == $cart_item['product_id']) {
                $already_in_cart = $cart_item['quantity'];
                break; // stop the loop if product is found
            }
        }
    }


    if (!is_null($new_max) && !empty($already_in_cart)) {
        if (($already_in_cart + $quantity) > $new_max) {
            // oops. too much.
            $passed = false;
            wc_add_notice(sprintf('This product has a limit of %1$s units per order', $already_in_cart), 'error');
        }
    }


    return $passed;
}
add_filter('woocommerce_add_to_cart_validation', 'validate_maximum_quantity_on_add_to_cart', 15, 5);

// Custom JS for accessories w/ variations validation on Kits
function variscite_inline_js_accessories_kit_validation()
{

    if (is_product()) {

        global $product;

        $product_id = $product->get_ID();
        $product_type = determine_product_type($product);

        if ($product_type == 'kit') {
            echo '
                <script type="text/javascript">
                    jQuery(".variations_form.cart").find(".single_add_to_cart_button").click(function(e) {
                        e.preventDefault();
                        
                        var ava_accessories = jQuery(".accessories_product_item"),
                            variation_selection_length,
                            is_valid = true;
                        
                        jQuery.each(ava_accessories, function() {
                            
                            if(jQuery(this).find(".custom_product_accessory:checked").length > 0) {
                                
                                if(jQuery(this).find(".accessory-attributes").length > 0) {
                                    
                                    variation_selection_length = jQuery(this).find(".accessory-attributes").find("input[type=\'radio\']:checked").length;
                                    
                                    if(variation_selection_length <= 0) {
                                        is_valid = false;
                                        jQuery(this).find(".variation-error").clone().appendTo(".single_variation_wrap .woocommerce-variation-add-to-cart");
                                        jQuery(this).find(".variation-error").show();
                                    }
                                }
                            }
                        });
                        
                        if(is_valid && !jQuery(this).hasClass("disabled")) {
                            jQuery(".variations_form.cart").submit();
                        }
                    });
                </script>
            ';
        }
    }
}
add_action('wp_footer', 'variscite_inline_js_accessories_kit_validation');

// Remove the nofollow link from the order button
function remove_rel($args, $product)
{
    unset($args['attributes']['rel']);
    return $args;
}
add_filter('woocommerce_loop_add_to_cart_args', 'remove_rel', 10, 2);

// Set global email params (to be used in the email template parts)
function variscite_emails_global_data($email_heading, $email)
{

    $GLOBALS['variscite_email_data'] = array(
        'order' => $email->object
    );
}
add_action('woocommerce_email_header', 'variscite_emails_global_data', 10, 2);

// Remove all states except for Canada and the US
function variscite_remove_irrelevant_states($states)
{

    $whitelisted = array();

    foreach ($states as $country_key => &$state) {

        if ($country_key === 'US' || $country_key === 'CA') {
            $whitelisted[$country_key] = $state;
        }
    }

    return $whitelisted;
}
add_filter('woocommerce_states', 'variscite_remove_irrelevant_states');

// Remove all states except for Canada and the US
function custom_country_locale_state_optional($locale)
{

    foreach ($locale as $country_code => &$state_field) {

        if (isset($locale[$country_code]['state']) && ($country_code !== 'US' && $country_code !== 'CA')) {
            $locale[$country_code]['state']['required'] = false;
        }
    }

    return $locale;
}
add_filter('woocommerce_get_country_locale', 'custom_country_locale_state_optional', 10, 1);

// Custom country validation
function variscite_custom_country_rules_js()
{
    if (is_checkout()) :
    ?>

        <script type="text/javascript">
            jQuery(document.body).on('checkout_error', function(e) {

                setTimeout(function() {
                    var errors = jQuery('.woocommerce-NoticeGroup-checkout').find('li');

                    errors.each(function() {

                        if (jQuery(this).html().toLowerCase().indexOf('sorry, your request isn\'t eligible for companies in israel') > -1) {
                            jQuery(this).addClass('geo-error');

                            setTimeout(function() {
                                jQuery('html, body').animate({
                                    scrollTop: 0
                                }, 500);
                            }, 100);
                        } else if (jQuery(this).html().toLowerCase().indexOf('ihre anfrage ist leider nicht für israelische unternehmen geeignet') > -1) {
                            jQuery(this).addClass('geo-error');

                            setTimeout(function() {
                                jQuery('html, body').animate({
                                    scrollTop: 0
                                }, 500);
                            }, 100);
                        }
                    });

                }, 250);

                setTimeout(function() {
                    jQuery('html, body').animate({
                        scrollTop: 0
                    }, 500);
                }, 100);
            });
        </script>

    <?php
    endif;
}
add_action('wp_footer', 'variscite_custom_country_rules_js');

function variscite_custom_country_rules_backend($fields, $errors)
{
    $billing = $fields['billing_country'];
    $shipping = $fields['shipping_country'];

    if (($billing == 'IL' && $shipping != 'IL') || ($billing != 'IL' && $shipping == 'IL')) {
        if (ICL_LANGUAGE_CODE == 'de') {
            $errors->add('validation', 'Ihre Anfrage ist leider nicht für israelische Unternehmen geeignet. Bitte wählen Sie bei Rechnungs- und Lieferadresse das gleiche Land aus oder kontaktieren Sie uns.');
        } else {
            $errors->add('validation', 'Sorry, your request isn\'t eligible for companies in Israel. Please choose the same billing and shipping country or contact us.');
        }
    }
}
add_action('woocommerce_after_checkout_validation', 'variscite_custom_country_rules_backend', 10, 2);

function variscite_disable_tax_for_mixed_il($taxes, $price, $rates, $price_includes_tax, $suppress_rounding)
{
    $shipping = isset(WC()->session->get('customer')['shipping_country']) ? WC()->session->get('customer')['shipping_country'] : '';
    $billing  = isset(WC()->session->get('customer')['country']) ? WC()->session->get('customer')['country'] : '';

    if (($billing == 'IL' && $shipping != 'IL') || ($billing != 'IL' && $shipping == 'IL')) {
        return array();
    }

    return $taxes;
}
add_filter('woocommerce_calc_tax', 'variscite_disable_tax_for_mixed_il', 10, 5);

// Force the default country to always be empty

add_filter('default_checkout_billing_country', 'change_default_checkout_country');
add_filter('default_checkout_shipping_country', 'change_default_checkout_country');

add_filter('default_checkout_country', 'change_default_checkout_country');
add_filter('default_checkout_state', 'change_default_checkout_state');
function change_default_checkout_country()
{
    return '';
}

function change_default_checkout_state()
{
    return '';
}

// On new order creation - fetch the session data and update the DB
function variscite_log_store_data($order_id)
{

    global $wpdb;
    //    $order_id = $order->get_id();
    $order = wc_get_order($order_id);

    //    $order_total = 0;
    //    $order_tax_total = 0;
    //
    //    foreach( $order->get_items('tax') as $item_id => $item ) {
    //        $order_total += $item->get_total();
    //	    $tax_total += $item->get_tax_total();
    //    }

    // Get the session items first
    $algo_cart_total              = WC()->session->get('algo_cart_total', 0);
    //    $cart_tax                     = WC()->session->get('cart_tax', 0);
    //    $cart_tax                     = $order_tax_total;
    $cart_tax                     = $order->get_total_tax();
    //    $ups_packages                 = WC()->session->get('ups_packages', 0);
    //    $ups_location                 = WC()->session->get('ups_location', 0);
    //    $ups_raw_request              = WC()->session->get('ups_raw_request', 0);
    //    $ups_response                 = WC()->session->get('ups_response', 0);
    $is_remote_location           = WC()->session->get('is_remote_location', 0);
    $calculated_rate              = WC()->session->get('calculated_rate', 0);
    //    $calculated_shipping_discount = WC()->session->get('calculated_shipping_discount', 0);
    $weights                      = WC()->session->get('weights', 0);
    $package_weight               = WC()->session->get('package_weight', 0);
    $customer_zone                = WC()->session->get('customer_zone', 0);
    $frieght_cost                 = WC()->session->get('freight_cost', 0);
    $fuel_cost                    = WC()->session->get('fuel_cost', 0);
    $insur                        = WC()->session->get('insur', 0);
    $reshimon                     = WC()->session->get('reshimon', 0);
    $safety_margin                = WC()->session->get('safety_margin', 0);
    $shipping_with_safety_margin  = WC()->session->get('shipping_with_safety_margin', 0);
    $offir                        = WC()->session->get('offir', 0);
    $discount                     = WC()->session->get('discount', 0);
    $cart_total                   = WC()->session->get('cart_total', 0);
    $without_safety_margin        = WC()->session->get('without_safety_margin', 0);
    $added_safety_margin          = WC()->session->get('added_safety_margin', 0);
    $weight_fix                    = WC()->session->get('weight_fix', 0);
    $sub_sum                      = ceil((float) get_field('vari__algo_zones-costs--freight-multi', 'option') * (floatval($frieght_cost) + floatval($fuel_cost) + floatval($insur) + floatval($reshimon)));

    // Get the order products
    $items = array();

    foreach ($order->get_items() as $item_id => $item) {
        $name = $item->get_name();
        $quantity = $item->get_quantity();
        $subtotal = $item->get_subtotal();
        $total = $item->get_total();
        $tax = $item->get_subtotal_tax();

        $items[] = array(
            'name'  => $name,
            'qty'   => $quantity,
            'sub'   => $subtotal,
            'total' => $total,
            'tax'   => $tax
        );
    }

    // Get the order's coupon codes
    $coupons = array();

    foreach ($order->get_coupon_codes() as $coupon_code) {
        $coupon = new WC_Coupon($coupon_code);
        $coupon_amount = $coupon->get_amount();

        $coupons[] = array(
            'code_used' => $coupon_code,
            'discount_amount' => $coupon_amount
        );
    }

    $_total = floatval($algo_cart_total) + floatval($calculated_rate);

    // Save everything in the DB
    $wpdb->insert('order_processing_log', array(
        'order_id'                     => $order_id,
        'ordered_items'                => json_encode($items),
        'cart_total'                   => $_total,
        'cart_tax'                     => $cart_tax,
        //        'ups_location'                 => $ups_location,
        //        'pack_config'                  => $ups_packages,
        //        'ups_raw_request'              => $ups_raw_request,
        //        'ups_raw_response'             => $ups_response,
        'calculated_rate'              => $calculated_rate,
        'coupons_used'                 => json_encode($coupons),
        //        'calculated_shipping_discount' => $calculated_shipping_discount,
        'is_remote_location'           => $is_remote_location ? 'true' : 'false',
        //        'is_remote_location'           => (strpos($ups_response, 'A Delivery Area Surcharge has been added to the service cost.') !== false ? 'true' : 'false'),
        'weights'                      => $weights,
        'weight_fix'                   => $weight_fix,
        'package_weight'               => $package_weight,
        'customer_zone'                => $customer_zone,
        'frieght_cost'                 => $frieght_cost,
        'fuel_cost'                    => $fuel_cost,
        'insur'                        => $insur,
        'reshimon'                     => $reshimon,
        'sub_sum'                      => $sub_sum,
        'safety_margin'                => $safety_margin,
        'without_safety_margin'        => $without_safety_margin,
        'added_safety_margin'          => $added_safety_margin,
        'shipping_with_safety_margin'  => $shipping_with_safety_margin,
        'offir'                        => $offir,
        'discount'                     => $discount,
        'cart_total'                   => $cart_total,
        'order_total'                  => $order->get_total(),
    ));
}
add_action('woocommerce_checkout_order_processed', 'variscite_log_store_data', 10, 3);
// (@AsafA)
// checkout order processed, som special offer set order pending and show message order
function variscite_woocommerce_checkout_order_processed($order_id, $posted_data, $order)
{

    // check if is special offer
    $som_special_offer = false;
    foreach ($order->get_items() as $item_id => $item) {
        if (
            $item->get_quantity() >= product_som_special_offer_quantity($item->get_id()) &&
            has_term('system-on-module', 'product_cat', $item->get_product_id()) &&
            get_field('product_display_som_special_offer', $item->get_product_id())
        ) {
            $som_special_offer = true;
            break;
        }
    }

    // if is special offer set order status pending and show message
    if ($som_special_offer) {
        $note = __('Pending Status SOM Special Offer.', 'variscite');
        $order->update_status('pending', $note);
        $order->add_order_note($note);
        $som_checkout_message_text = get_field('som_checkout_message_text', 'option');
        $som_checkout_message_text = $som_checkout_message_text ? $som_checkout_message_text : 'Thanks for placing your order. Our sales team will contact you to proceed with the payment process.';
        throw new Exception(__($som_checkout_message_text, 'variscite'));
    }
	?>
    <script>
        window.dataLayer = window.dataLayer || [];
        window.dataLayer.push({
            'event': 'purchase',
            'customer_id': '',
            'email': '<?php echo $order->get_billing_email(); ?>',
            'phone_number': '<?php echo $order->get_billing_phone(); ?>',
            'address.first_name': '<?php echo $order->get_billing_first_name(); ?>',
			'address.last_name': '<?php echo $order->get_billing_last_name(); ?>',
       });
    </script>
    <?php
}
add_action('woocommerce_checkout_order_processed', 'variscite_woocommerce_checkout_order_processed', 10, 3);

function variscite_display_log()
{

    if (is_user_logged_in() && current_user_can('administrator') && isset($_GET['logdebug'])) {
        global $wpdb;

        $query = 'SELECT * FROM order_processing_log';

        if (isset($_GET['download'])) {
            $query .= ' ORDER BY order_id DESC';
        }

        //        else if (! isset($_GET['all']) && ! isset($_GET['orderid'])) {
        else if (!isset($_GET['orderid'])) {
            $query .= ' ORDER BY order_id DESC LIMIT 50';
        } else if (isset($_GET['orderid'])) {
            //        if(isset($_GET['orderid'])) {
            $query .= ' WHERE order_id="' . esc_html($_GET['orderid']) . '"';
        } else {
            $query .= ' ORDER BY order_id DESC';
        }

        $results = $wpdb->get_results($query);

        if (isset($_GET['download'])) {
            $file = "all_order.txt";
            $txt = fopen($file, "w") or die("Unable to open file.");
            fwrite($txt, print_r($results, true));

            header('Content-Description: File Transfer');
            header('Content-Disposition: attachment; filename=' . basename($file));
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            header("Content-Type: text/plain");
            readfile($file);

            //            echo "Downloading file...";
            die();

            //            header('Content-Type: application/octetstream; name="variscite-shop-log-' . date('Y-m-d') . '.txt"');
            //            header('Content-Type: application/octet-stream; name="variscite-shop-log-' . date('Y-m-d') . '.txt"');
            //            header('Content-Disposition: attachment; filename="variscite-shop-log-' . date('Y-m-d') . '.txt"');
        }

        print("<pre>" . print_r($results, true) . "</pre>");
        //        foreach( $results[0] as $key => $value ) {
        //            echo $key . ":<br/>" . $value . "<br/><br/>";
        //        }
        die();
    }
}
add_action('init', 'variscite_display_log');

// Add the SFDC hidden fields to the checkout page
function variscite_add_sfdc_marketing_hidden_checkout_fields($fields)
{

    $fields['billing']['curl'] = array(
        'label'       => 'curl',
        'type'        => 'text',
        'placeholder' => '',
        'required'    => false,
        'class'       => array('is-visually-hidden'),
        'clear'       => true,
        'default'     => 'not_specified'
    );

    $fields['billing']['Campaign_medium__c'] = array(
        'label'       => 'Campaign_medium__c',
        'type'        => 'text',
        'placeholder' => '',
        'required'    => false,
        'class'       => array('is-visually-hidden'),
        'clear'       => true,
        'default'     => 'not_specified'
    );

    $fields['billing']['Campaign_source__c'] = array(
        'label'       => 'Campaign_source__c',
        'type'        => 'text',
        'placeholder' => '',
        'required'    => false,
        'class'       => array('is-visually-hidden'),
        'clear'       => true,
        'default'     => 'not_specified'
    );

    $fields['billing']['Campaign_content__c'] = array(
        'label'       => 'Campaign_content__c',
        'type'        => 'text',
        'placeholder' => '',
        'required'    => false,
        'class'       => array('is-visually-hidden'),
        'clear'       => true,
        'default'     => 'not_specified'
    );

    $fields['billing']['Campaign_term__c'] = array(
        'label'       => 'Campaign_term__c',
        'type'        => 'text',
        'placeholder' => '',
        'required'    => false,
        'class'       => array('is-visually-hidden'),
        'clear'       => true,
        'default'     => 'not_specified'
    );

    $fields['billing']['Page_url__c'] = array(
        'label'       => 'Page_url__c',
        'type'        => 'text',
        'placeholder' => '',
        'required'    => false,
        'class'       => array('is-visually-hidden'),
        'clear'       => true,
        'default'     => 'not_specified'
    );

    $fields['billing']['Paid_Campaign_Name__c'] = array(
        'label'       => 'Paid_Campaign_Name__c',
        'type'        => 'text',
        'placeholder' => '',
        'required'    => false,
        'class'       => array('is-visually-hidden'),
        'clear'       => true,
        'default'     => 'not_specified'
    );

    $fields['billing']['GA_id__c'] = array(
        'label'       => 'GA_id__c',
        'type'        => 'text',
        'placeholder' => '',
        'required'    => false,
        'class'       => array('is-visually-hidden'),
        'clear'       => true,
        'default'     => 'not_specified'
    );

    return $fields;
}
add_filter('woocommerce_checkout_fields', 'variscite_add_sfdc_marketing_hidden_checkout_fields');

// Saving the hidden field value in the order metadata
function variscite_save_sfdc_marketing_hidden_checkout_fields($order_id, $data)
{

    if (!empty($data['curl'])) {
        update_post_meta($order_id, '_curl', sanitize_text_field($data['curl']));
    }

    if (!empty($data['Campaign_medium__c'])) {
        update_post_meta($order_id, '_Campaign_medium__c', sanitize_text_field($data['Campaign_medium__c']));
    }

    if (!empty($data['Campaign_source__c'])) {
        update_post_meta($order_id, '_Campaign_source__c', sanitize_text_field($data['Campaign_source__c']));
    }

    if (!empty($data['Campaign_term__c'])) {
        update_post_meta($order_id, '_Campaign_term__c', sanitize_text_field($data['Campaign_term__c']));
    }

    if (!empty($data['Page_url__c'])) {
        update_post_meta($order_id, '_Page_url__c', sanitize_text_field($data['Page_url__c']));
    }

    if (!empty($data['Campaign_content__c'])) {
        update_post_meta($order_id, '_Campaign_content__c', sanitize_text_field($_POST['Campaign_content__c']));
    }

    if (!empty($data['Paid_Campaign_Name__c'])) {
        update_post_meta($order_id, '_Paid_Campaign_Name__c', sanitize_text_field($data['Paid_Campaign_Name__c']));
    }

    if (!empty($data['GA_id__c'])) {
        update_post_meta($order_id, '_GA_id__c', sanitize_text_field($data['GA_id__c']));
    }
}
add_action('woocommerce_checkout_update_order_meta', 'variscite_save_sfdc_marketing_hidden_checkout_fields', 10, 2);

function variscite_visually_hidden_fields()
{
    ?>

    <style>
        .is-visually-hidden {
            opacity: 0;
            position: absolute !important;
            top: 0;
            left: 0;
        }
    </style>

    <script>
        jQuery(window).on('load', function() {
            prefill_from_localstorage();
        });

        var updated_checkout_from_localStorage = false;
        jQuery('body').on('update_checkout update_order_review', function() {

            setTimeout(function() {
                if (!updated_checkout_from_localStorage) {
                    updated_checkout_from_localStorage = true;
                    prefill_from_localstorage();
                }
            }, 250);
        });

        function prefill_from_localstorage() {

            if (localStorage.hasOwnProperty('sf_medium')) {

                jQuery('[name="Campaign_medium__c"]').val(
                    localStorage.getItem('sf_medium')
                ).attr('value', localStorage.getItem('sf_medium'));
            }

            if (localStorage.hasOwnProperty('sf_source')) {

                jQuery('[name="Campaign_source__c"]').val(
                    localStorage.getItem('sf_source')
                ).attr('value', localStorage.getItem('sf_source'));
            }

            if (localStorage.hasOwnProperty('sf_term')) {

                jQuery('[name="Campaign_term__c"]').val(
                    localStorage.getItem('sf_term')
                ).attr('value', localStorage.getItem('sf_term'));
            }

            if (localStorage.hasOwnProperty('sf_campaign')) {

                jQuery('[name="Paid_Campaign_Name__c"]').val(
                    localStorage.getItem('sf_campaign')
                ).attr('value', localStorage.getItem('sf_campaign'));
            }

            if (localStorage.hasOwnProperty('sf_content')) {

                jQuery('[name="Campaign_content__c"]').val(
                    localStorage.getItem('sf_content')
                ).attr('value', localStorage.getItem('sf_content'));
            }

            // jQuery('input[name="GA_id__c"]').val(ga.getAll()[0].get('clientId')).attr('value', ga.getAll()[0].get('clientId'));
            jQuery('input[name="Page_url__c"]').val(window.location.href).attr('value', window.location);
            jQuery('input[name="curl"]').val(window.location.href).attr('value', window.location);
        }
    </script>

    <?php
}
add_action('wp_footer', 'variscite_visually_hidden_fields');

function vari_get_cart_items_count()
{
    die(json_encode(array('total' => WC()->cart->get_cart_contents_count())));
}
add_action('wp_ajax_vari_get_cart_items_count', 'vari_get_cart_items_count');
add_action('wp_ajax_nopriv_vari_get_cart_items_count', 'vari_get_cart_items_count');

// Add different shop dataLayer events
function vari_datalayer_events()
{

    // Product page load
    if (is_singular('product')) :
        $product = wc_get_product(get_the_ID());

        $categories  = array();
        $product_cat = wp_get_post_terms($product->get_id(), 'product_cat');
        foreach ($product_cat as $cat) $categories[] = $cat->slug;

        if (in_array('evaluation-kit', $categories)) {
            $parent_product_type = 'Evaluation Kit';
        } else if (in_array('system-on-module', $categories)) {
            $parent_product_type = 'System on Module';
        } else {
            $parent_product_type = 'Accessory';
        }
    ?>

        <script>
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                "ecommerce": {
                    "detail": {
                        "products": [{
                            "id": "<?php echo get_the_ID(); ?>",
                            "name": "<?php echo get_the_title(); ?>",
                            "price": "<?php echo $product->get_price(); ?>",
                            "brand": "Variscite",
                            "category": "<?php echo $parent_product_type; ?>"
                        }]
                    }
                }
            });
        </script>

        <?php
        $product_id   = get_the_ID();
        $product      = wc_get_product($product_id);
        $pretty_vars  = array();

        if ($product->is_type('variable')) {
            $handle = new WC_Product_Variable($product_id);
            $vars = $handle->get_children();
        }

        $categories  = array();
        $product_cat = wp_get_post_terms($product_id, 'product_cat');

        foreach ($product_cat as $cat) $categories[] = $cat->slug;

        if (in_array('evaluation-kit', $categories)) {
            $parent_product_type = 'Evaluation Kit';

            foreach ($vars as $value) {
                $single_variation = new WC_Product_Variation($value);
                $var_atts = $single_variation->get_variation_attributes();

                $pretty_vars[$value] = array(
                    'kit' => get_term_by('slug', $var_atts['attribute_pa_kit'], 'pa_kit')->name,
                    'som' => get_term_by('slug', $var_atts['attribute_pa_som-configuration'], 'pa_som-configuration')->name,
                    'os'  => get_term_by('slug', $var_atts['attribute_pa_operating-system'], 'pa_operating-system')->name
                );
            }
        } else if (in_array('system-on-module', $categories)) {
            $parent_product_type = 'System on Module';

            foreach ($vars as $value) {
                $single_variation = new WC_Product_Variation($value);
                $var_atts = $single_variation->get_variation_attributes();

                $pretty_vars[$value] = array(
                    'som' => get_term_by('slug', $var_atts['attribute_pa_som-configuration'], 'pa_som-configuration')->name
                );
            }
        } else {
            $parent_product_type = 'Accessory';
        }

        $product_config = array(
            'id'    => $product_id,
            'name'  => get_the_title($product_id),
            'price' => $product->get_price(),
            'type'  => $parent_product_type,
            'vars'  => $pretty_vars
        );

        wp_register_script('add_to_cart_event', get_stylesheet_directory_uri() . '/js/add-to-cart-listener.js', array('jquery'));
        wp_enqueue_script('add_to_cart_event');
        wp_localize_script('add_to_cart_event', 'product_config', $product_config);

    endif;
}
add_action('wp_footer', 'vari_datalayer_events');

// Add a custom validation to the checkout page for cases where the
// shipping total is 0 or lower
function vari_checkout_validate_shipping_cost_is_correct($fields)
{

    // Check if exceeded the amount of tries for finding a non-zero field
    // in the shipping algo
    $exceeded_tries = WC()->session->get('exceeded_tries');
    $exceeded_field = WC()->session->get('exceeded_field');
    if ($exceeded_tries) {

        // Add an error to the checkout page
        //$errors->add( 'validation', 'An unexpected error has occurred. Please contact us via the website or email at <a href="mailto:sales@variscite.com">sales@variscite.com</a>' );

        if (!isset($_COOKIE['error_lead_id']) || !$_COOKIE['error_lead_id']) {

            // Collect all billing fields for the new order lead
            $billing_address = array(
                'first_name' => $fields['billing_first_name'],
                'last_name'  => $fields['billing_last_name'],
                'company'    => $fields['billing_company'],
                'email'      => $fields['billing_email'],
                'phone'      => $fields['billing_phone'],
                'address_1'  => $fields['billing_address_1'],
                'address_2'  => $fields['billing_address_2'],
                'city'       => $fields['billing_city'],
                'state'      => $fields['billing_state'],
                'postcode'   => $fields['billing_postcode'],
                'country'    => $fields['billing_country']
            );

            // Collect all shipping fields for the new order lead
            $shipping_address = array(
                'first_name' => $fields['shipping_first_name'],
                'last_name'  => $fields['shipping_last_name'],
                'company'    => $fields['shipping_company'],
                'email'      => $fields['shipping_email'],
                'phone'      => $fields['shipping_phone'],
                'address_1'  => $fields['shipping_address_1'],
                'address_2'  => $fields['shipping_address_2'],
                'city'       => $fields['shipping_city'],
                'state'      => $fields['shipping_state'],
                'postcode'   => $fields['shipping_postcode'],
                'country'    => $fields['shipping_country']
            );

            // Create the order and add all the data to it
            $checkout = WC()->checkout();
            $order_id = $checkout->create_order(array());
            $order = wc_get_order($order_id);
            update_post_meta($order_id, '_customer_user', get_current_user_id());
            $order->calculate_totals();
            $order->set_address($billing_address, 'billing');
            $order->set_address($shipping_address, 'shipping');

            // Change the order's status to "Lead"
            $order->update_status('wc-lead');
            $order->add_order_note('This order is a lead');
            $order->save();

            $api_to_lead = new wooToSFDC_api_to_lead();
            $api_to_lead->create_new_lead($order_id);

            // Save the order lead id in a cookie
            setcookie('error_lead_id', $order_id, time() + (86400 * 14), "/"); // 86400 = 1 day
        } else {
            $order_id = $_COOKIE['error_lead_id'];
        }

        // Also, dispatch an email about the error w/ the order's details
        $to      = array('sales@variscite.com', 'lena.g@variscite.com', 'ayelet.o@variscite.com', 'eden.d@variscite.com', 'allon@designercoded.com', 'roi@designercoded.com', 'avihu.h@variscite.com');
        //        $to      = array('omer@problemsolver.co.il');
        $subject = "A '0' value was found in the shipping calculation for order number $order_id";
        $message = "The following values are recognized as '0' on the order number $order_id";
        $message = "The field with value of '0' is $exceeded_field";
        checkout_error_email($fields, $to, $subject, $message);
    } else {
        // Get the total shipping price and country (to exclude IL from the errors)
        $total_shipping   = (float) WC()->cart->shipping_total;
        $shipping_country = WC()->customer->get_shipping_country();

        if ($total_shipping <= -1 && strtolower($shipping_country) !== 'il') {

            // Display the validation error
            //$errors->add('validation', 'An error occurred. Please send your order to orders@variscite.com.');

            // Also, dispatch an email about the error w/ the order's details
            $to      = array('sales@variscite.com', 'lena.g@variscite.com', 'ayelet.o@variscite.com', 'eden.d@variscite.com', 'allon@designercoded.com', 'roi@designercoded.com', 'avihu.h@variscite.com');
            //            $to      = array('omer@problemsolver.co.il');
            $subject = 'Variscite Shop: Shipping cost calculation error';
            $message = 'A user has tried to submit the order attached below on the Variscite Shop, but the shipping calculation returned a sum of 0. A relevant error was displayed for the user.';
            checkout_error_email($fields, $to, $subject, $message);
        }
    }
}
add_action('woocommerce_after_checkout_validation', 'vari_checkout_validate_shipping_cost_is_correct', 10);

function checkout_error_email($fields, $to, $subject, $message = '', $order_id = 0)
{
    $body    = "
            <strong>$message</strong><br>
            Billing First name: {$fields['billing_first_name']}<br>
            Billing Last name: {$fields['billing_last_name']}<br>
            Billing Company name: {$fields['billing_company']}<br>
            Billing Email: {$fields['billing_email']}<br>
            Billing Phone: {$fields['billing_phone']}<br>
            Billing Country: {$fields['billing_country']}<br>
            Registration number: {$_POST['billing_company_reg_number']}<br>
            Billing Address: {$fields['billing_address_1']} {$fields['billing_address_2']}<br>
            Billing State: {$fields['billing_state']}<br>
            Billing City: {$fields['billing_city']}<br>
            Billing ZIP Code: {$fields['billing_postcode']}<br>
            Shipping First name:{$fields['shipping_first_name']} <br>
            Shipping Last name: {$fields['shipping_last_name']}<br>
            Shipping Company name: {$fields['shipping_company']}<br>
            Shipping Email: {$fields['shipping_email']}<br>
            Shipping Phone: {$fields['shipping_phone']}<br>
            Shipping Country: {$fields['shipping_country']}<br>
            Shipping Address: {$fields['shipping_address_1']} {$fields['shipping_address_2']}<br>
            Shipping City: {$fields['shipping_city']}<br>
            Shipping State: {$fields['shipping_state']}<br>
            Shipping ZIP Code: {$fields['shipping_postcode']}<br>
            Notes: {$fields['billing_customer_note']}<br>
            Estimated project quantities: {$_POST['estimated_product_quantities']}<br>
            <br>
            <u>Ordered items:</u><br>
        ";
	
	$cart = WC()->cart;
	//check for empty cart
	if( $cart->is_empty() ){
		global $woocommerce;
		$cart = $woocommerce->cart;
	}
	$items = $cart->get_cart();
	
	//get cart items from session if empty
	if( empty( $items ) ){
		$items = WC()->session->get( 'cart' );
	}
	
	if( $items ){
		foreach( $items as $cart_item_key => $cart_item ){
			$product = $cart_item['data'];
			$price   = WC()->cart->get_product_price($product);
			$total   = WC()->cart->get_product_subtotal($product, $cart_item['quantity']);
			$name    = $cart_item['data']->get_data()['name'];
			$sku     = $cart_item['data']->get_data()['sku'];
			$config  = $cart_item['data']->get_data()['attribute_summary'];

			$body .= "
					Name: $name<br>
					SKU: $sku<br>
					Configuration: $config<br>
					Price: $price<br>
					Quantity: {$cart_item['quantity']}<br>
					Total: $total<br>
				";
		}
	}
	
    $body .= '<br> Order Total: ' . WC()->cart->get_cart_total();

    /* $fa = fopen( ABSPATH.'alog-zero-dbg.log', 'a+' );
    fwrite( $fa, '-----'.date( 'Y-m-d H:i:s' ).'-----' );
    fwrite( $fa, PHP_EOL );
    fwrite( $fa, $subject );
    fwrite( $fa, PHP_EOL );
    fwrite( $fa, $body );
    fwrite( $fa, PHP_EOL );
    fwrite( $fa, PHP_EOL );
    fclose( $fa ); */

    wp_mail($to, $subject, $body, array(
        'Content-Type: text/html; charset=UTF-8'
    ));
}

// Add a body class to non-purchasable products
function variscite_non_purchasable_add_body_class($classes)
{

    if (is_singular('product') && get_field('variscite__product_is_not_purchasable')) {
        $classes[] = 'single-product--non-purchasable';
    }

    return $classes;
}
add_action('body_class', 'variscite_non_purchasable_add_body_class');

// Exclude non-purchasable products from the shop loop
function variscite_non_purchasable_exclude_from_loop($q)
{
    $meta_query = (array) $q->get('meta_query');

    $meta_query[] = array(
        array(
            'relation' => 'OR',
            array(
                'key' => 'variscite__product_is_excluded',
                'compare' => 'NOT EXISTS'
            ),
            array(
                'key' => 'variscite__product_is_excluded',
                'value' => '0'
            )
        )
    );

    $q->set('meta_query', $meta_query);
}
add_action('woocommerce_product_query', 'variscite_non_purchasable_exclude_from_loop');


// Create the 'Lead' post type
if (!function_exists('variscite_lead_custom_post_type')) {

    function variscite_lead_custom_post_type()
    {

        $labels = array(
            'name' => _x('Leads', 'Post Type General Name', 'variscite'),
            'singular_name' => _x('Lead', 'Post Type Singular Name', 'variscite'),
            'menu_name' => __('Leads', 'variscite'),
            'name_admin_bar' => __('Leads', 'variscite'),
            'archives' => __('Archive', 'variscite'),
            'parent_item_colon' => __('Post parent:', 'variscite'),
            'all_items' => __('All leads', 'variscite'),
            'add_new_item' => __('New lead', 'variscite'),
            'add_new' => __('New lead', 'variscite'),
            'new_item' => __('New lead', 'variscite'),
            'edit_item' => __('Edit lead', 'variscite'),
            'update_item' => __('Update lead', 'variscite'),
            'view_item' => __('View lead', 'variscite'),
            'search_items' => __('Search leads', 'variscite'),
            'not_found' => __('No leads found', 'variscite'),
            'not_found_in_trash' => __('No leads found', 'variscite'),
            'featured_image' => __('Featured image', 'variscite'),
            'set_featured_image' => __('Set featured image', 'variscite'),
            'remove_featured_image' => __('Remove featured image', 'variscite'),
            'use_featured_image' => __('Use as featured image', 'variscite'),
            'insert_into_item' => __('Insert into lead', 'variscite'),
            'uploaded_to_this_item' => __('Upload to this lead', 'variscite'),
            'items_list' => __('Leads list', 'variscite'),
            'items_list_navigation' => __('Leads list navigation', 'variscite'),
            'filter_items_list' => __('Filter leads', 'variscite'),
        );

        $args = array(
            'label' => __('Leads', 'variscite'),
            'description' => __('Leads', 'variscite'),
            'labels' => $labels,
            'supports' => array('title', 'custom-fields', 'page-attributes'),
            'taxonomies' => array(),
            'hierarchical' => true,
            'public' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'menu_position' => 7,
            'menu_icon' => 'dashicons-index-card',
            'show_in_admin_bar' => true,
            'show_in_nav_menus' => true,
            'can_export' => true,
            'has_archive' => false,
            'exclude_from_search' => false,
            'publicly_queryable' => false,
            'show_in_rest' => true,
            'capability_type' => 'page',
            'rewrite' => false,
            'query_var' => false
        );

        register_post_type('form-lead', $args);
    }

    add_action('init', 'variscite_lead_custom_post_type', 0);
}

// Custom columns for the admin view of the lead post type
function set_custom_edit_form_lead_columns($columns)
{

    $columns['fullname'] = 'Name';
    $columns['company']  = 'Company';
    $columns['country']  = 'Country';
    $columns['phone']    = 'Phone';
    $columns['status']   = 'Status';

    unset($columns['date']);
    $columns['date'] = 'Date';

    return $columns;
}
add_filter('manage_form-lead_posts_columns', 'set_custom_edit_form_lead_columns');

// Add the data to the custom columns for the book post type:
function custom_form_lead_columns($column, $post_id)
{

    switch ($column) {

        case 'fullname':
            echo get_field('variscite__leads-FirstName', $post_id) . ' ' . get_field('variscite__leads-LastName', $post_id);
            break;

        case 'company':
            echo get_field('variscite__leads-Company', $post_id);
            break;

        case 'country':
            echo get_field('variscite__leads-Country', $post_id);
            break;

        case 'phone':
            echo get_field('variscite__leads-Phone', $post_id);
            break;

        case 'status':
            $sent_to_sf = get_field('variscite__leads-sfdc', $post_id);
            $sent_email = get_field('variscite__leads-email-sent', $post_id);
            $is_sfdc  = $sent_to_sf ? 'check' : 'times';
            $is_email = $sent_email ? 'check' : 'times';
            $sfdc_style = $sent_to_sf ? 'margin: 0 10px;' : 'margin: 0 10px; color: red';
            $email_style = $sent_email ? 'margin: 0 10px;' : 'margin: 0 10px; color: red';

            echo "<span style=\"margin: 0 10px;\"><i style='margin: 0 5px 0 0;' class=\"fa fa-check\"></i>Created</span><span style=\"$sfdc_style\"><i style='margin: 0 5px 0 0;' class=\"fa fa-$is_sfdc\"></i>Salesforce</span><span style=\"$email_style\"><i style='margin: 0 5px 0 0;' class=\"fa fa-$is_email\"></i>Email</span>";
            break;
    }
}
add_action('manage_form-lead_posts_custom_column', 'custom_form_lead_columns', 10, 2);

// Add FontAwesome to the admin dashboard
function fontawesome_dashboard()
{
    wp_enqueue_style('fontawesome', 'https://use.fontawesome.com/releases/v5.8.1/css/all.css', '', '5.8.1', 'all');
}
add_action('admin_init', 'fontawesome_dashboard');

// Force content-type HTML on all outgoing emails
function variscite_emails_set_content_type()
{
    return 'text/html';
}
add_filter('wp_mail_content_type', 'variscite_emails_set_content_type');


//Add a new country to countries list

add_filter('woocommerce_countries',  'add_new_country');
function add_new_country($countries)
{
    $new_countries = array(
        'BY'  =>  'Belarus', 'woocommerce',
        'AM'  =>  'Armenia', 'woocommerce',
        'KZ'  =>  'Kazakhstan', 'woocommerce',
        'KG'  =>  'Kyrgyzstan', 'woocommerce',
        'MD'  =>  'Moldova', 'woocommerce',
        'RU'  =>  'Russia', 'woocommerce',
        'TJ'  =>  'Tajikistan', 'woocommerce',
        'TM'  =>  'Turkmenistan', 'woocommerce',
        'UA'  =>  'Ukraine', 'woocommerce',
        'UZ'  =>  'Uzbekistan', 'woocommerce',
        'CU'  =>  'Cuba', 'woocommerce',
        'GE'  =>  'Georgia', 'woocommerce',
        'IQ'  =>  'Iraq', 'woocommerce',
        'IR'  =>  'Iran', 'woocommerce',
        'LA'  =>  'Laos', 'woocommerce',
        'LY'  =>  'Libya', 'woocommerce',
        'MO'  =>  'Macau', 'woocommerce',
        'MN'  =>  'Mongolia', 'woocommerce',
        'KP'  =>  'North Korea', 'woocommerce',
        'SD'  =>  'Sudan', 'woocommerce',
        'SY'  =>  'Syria', 'woocommerce',
        'AE'  =>  'United Arab Emirates', 'woocommerce',
    );

    return array_merge($countries, $new_countries);
}

add_filter('woocommerce_continents', 'add_country_to_continents');
function add_country_to_continents($continents)
{
    $continents['EU']['countries'][] = 'BY, MD, RU, UA';
    $continents['AS']['countries'][] = 'AM, KZ, KG, TJ, TM, UZ, GE, IQ, IR, MO, MN, KP, SY, AE';
    $continents['AF']['countries'][] = 'SD, LY';
    $continents['SA']['countries'][] = 'CU';
    return $continents;
}


//disable auto plugins updates///
add_filter('auto_update_plugin', '__return_false');

function verify_filters()
{
    if (!is_admin()) {
        if (isset($_GET['cpu_name'])) {
            $cpu_names = explode(",", $_GET['cpu_name']);
            foreach ($cpu_names as $cpu_name) {
                if (!term_exists($cpu_name)) {
                    // header('Location: ' . get_home_url());
                    header("HTTP/1.0 410 Gone");
                    exit;
                }
            }
        }
        if (isset($_GET['product_type'])) {
            $product_types = explode(",", $_GET['product_type']);
            foreach ($product_types as $product_type) {
                if (!term_exists($product_type)) {
                    // header('Location: ' . get_home_url());
                    header("HTTP/1.0 410 Gone");
                    exit;
                }
            }
        }
    }
}
add_action('send_headers', 'verify_filters');

add_filter('woocommerce_shipping_calculator_enable_state', '__return_false');

//add_filter( 'woocommerce_checkout_fields', 'misha_no_email_validation' );
//
//function misha_no_email_validation( $fields ){
//
//    unset( $fields['billing']['billing_country'] );
//    return $fields;
//
//}
//
//add_filter( 'woocommerce_default_address_fields' , 'custom_override_default_address_fields' );
//
//// Our hooked in function - $address_fields is passed via the filter!
//function custom_override_default_address_fields( $address_fields ) {
//    $address_fields['billing']['billing_country']['required'] = false;
//
//    return $address_fields;
//}

// Fix canonicals on home page with filters (GET parameters)
//function omer_fix_canonicals() {
//    if ( is_front_page() && count($_GET) > 0 ) {
//        echo '<link rel="canonical" href="'.get_home_url().'/">';
//    }
//}
//add_action( 'wp_head', 'omer_fix_canonicals' );

// Stop country autofill on checkout
function omer_disable_autofill_for_field($field)
{

    $agent = $_SERVER['HTTP_USER_AGENT'];

    if (strpos($agent, 'Firefox') !== false) {
        $field = str_replace('autocomplete="country"', 'autocomplete="off"', $field);
        return $field;
    } else {
        $field = str_replace('autocomplete="country"', 'autocomplete="none"', $field);
        return $field;
    }
}
add_filter('woocommerce_form_field', 'omer_disable_autofill_for_field', 1, 1);

function cl_acf_set_language()
{
    return acf_get_setting('default_language');
}

function get_global_option($name)
{
    add_filter('acf/settings/current_language', 'cl_acf_set_language', 100);
    $option = get_field($name, 'option');
    remove_filter('acf/settings/current_language', 'cl_acf_set_language', 100);
    return $option;
}

// Register New Order Statuses
function register_custom_order_statuses()
{
    register_post_status('wc-lead', array(
        'label'                     => _x('Lead', 'WooCommerce Order status', 'text_domain'),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop('Lead (%s)', 'Lead (%s)', 'text_domain')
    ));
}
add_filter('init', 'register_custom_order_statuses');

// Add New Order Statuses to WooCommerce
function add_custom_order_statuses($order_statuses)
{
    $order_statuses['wc-lead'] = _x('Lead', 'WooCommerce Order status', 'text_domain');
    return $order_statuses;
}
add_filter('wc_order_statuses', 'add_custom_order_statuses');

// Add brand to YoastSEO's product schema
add_filter('wpseo_schema_webpage', 'example_change_webpage');

/**
 * Changes @type of Webpage Schema data.
 *
 * @param array $data Schema.org Webpage data array.
 *
 * @return array Schema.org Webpage data array.
 */
function example_change_webpage($data)
{

    // Check if this is a product page
    if (!is_product()) {
        return $data;
    }

    // Set a Brand to all products
    $brand = 'Variscite';

    $data['brand'] = array(
        '@type' => 'Brand',
        'name'  => $brand,
    );
    return $data;
}

// Add the custom columns to the shop_order post type:
function set_custom_edit_shop_order_columns($columns)
{
    $columns['sent_to_sf'] = 'Sent to SF';
    return $columns;
}
add_filter('manage_shop_order_posts_columns', 'set_custom_edit_shop_order_columns', 999);

// Add the data to the custom columns for the shop_order post type:
function custom_shop_order_column($column, $post_id)
{
    switch ($column) {
        case 'sent_to_sf':
            $lead_id = get_post_meta($post_id, '_lead_id', true);
            $sf_error = get_field('order-salesforce_error', $post_id);
            if ($sf_error || empty($lead_id)) {
        ?>
                <span style="margin: 0 10px; color: red">
                    <i style="margin: 0 5px 0 0;" class="fa fa-times"></i>
                    SF Error
                </span>
            <?php
            } else {
            ?>
                <span style="margin: 0 10px;">
                    <i style="margin: 0 5px 0 0;" class="fa fa-check"></i>
                    Sent to SF
                </span>
    <?php
            }
            break;
    }
}
add_action('manage_shop_order_posts_custom_column', 'custom_shop_order_column', 999, 2);



/*********************************************
 ** COOKIE NOTICE POPUP
 *********************************************/
function cookie_notice_vari()
{
    $opt = get_field('cookie_pop-up_settings', 'option');
    ?>
    <div class="cookie-notice" style="background-color: <?php echo isset($opt['cookies_popup_color']) ? $opt['cookies_popup_color'] : ''; ?> ; color: <?php echo isset($opt['cookies_popup_text_color']) ? $opt['cookies_popup_text_color'] : ''; ?>">
        <div class="cookie-notice__container">
            <div class="cookie-notice__row">
                <div class="cookie-notice__left-col">
                    <p><?php _e("Dear user, by continuing to use our site, you consent to our cookies policy. Please review", "Variscite_Privacy"); ?> <a href="https://www.variscite.com/privacy-policy/" title="Variscite Privacy Policy" style="color: <?php echo isset($opt['cookies_popup_text_color']) ? $opt['cookies_popup_text_color'] : ''; ?>"><?php _e("Variscite Privacy Policy", "Variscite_Privacy"); ?></a><?php _e(" to learn how they can be disabled but notice that some features of the site will not work.", "Variscite_Privacy"); ?></p>
                </div>
                <div class="cookie-notice__right-col">
                    <div class="cookie-notice__action">
                        <span class="close-cookie-notice close-cookie-notice--text" style="background-color: <?php echo isset($opt['cookies_popup_button_color']) ? $opt['cookies_popup_button_color'] : ''; ?> ; color: <?php echo isset($opt['cookies_popup_text_color']) ? $opt['cookies_popup_text_color'] : ''; ?>">
                            <?php _e("Accept cookies", "Variscite_Privacy"); ?>
                            <i class="fa fa-times"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
//add_action('wp_footer', 'cookie_notice_vari', 99);

add_filter('woocommerce_cart_shipping_method_full_label', function ($label, $method) {
    if ($method->method_id == "local_pickup")
        return $label;

    $has_cost = 0 < $method->cost;
    if (!$has_cost) {
        $label = wc_price(0);
    }
    return $label;
}, 10, 2);

add_action('woocommerce_after_shipping_error', function ($method) {
    $has_cost = 0 < $method->cost;
    if (!$has_cost) {
        echo '<tr class="shipping_error"><td colspan="2" style="padding-top:20px;padding-bottom:20px;color: #c2021b;    padding-right: 0;"><span class="error-note">' . __('An unexpected error has occurred. Please contact us via the website or email at <a href="mailto:sales@variscite.com">sales@variscite.com</a>', "shipping-algo") . '</span><td></tr>';
    }
}, 10);

// (@AsafA)
function acf_load_product_display_som_special_offer_product_variation_field_choices($field)
{

    $product_id = get_the_ID();
    if ($product_id && get_post_type($product_id) == 'product') {
        $field['choices'][''] = $field['label'];
        $default_language = apply_filters('wpml_default_language', NULL);
        $product_id = apply_filters('wpml_object_id', $product_id, 'product', FALSE, $default_language);
        $product = wc_get_product($product_id);
        $prod_variations = $product->get_variation_attributes();
        // loop through array and add to field 'choices'
        if (is_array($prod_variations)) {
            foreach ($prod_variations as $taxonomy => $terms) {
                foreach ($terms as $term_slug) {
                    $term = get_term_by('slug', $term_slug, $taxonomy);
                    $field['choices'][$term->term_id] = $term->name;
                }
            }
        }

        // if ($product_display_som_special_offer_product_variation = get_field('product_display_som_special_offer_product_variation', $product_id)) {
        //     $field['default_value'] =  $field['choices'][ $product_display_som_special_offer_product_variation ];
        // }
    }

    return $field;
}
add_filter('acf/load_field/name=product_display_som_special_offer_product_variation', 'acf_load_product_display_som_special_offer_product_variation_field_choices');

include('inc/paypal-gateway.php');
//include( 'inc/send-order-emails.php' );

//add data into woo checkout
add_filter('woocommerce_form_field_args', function ($args, $key, $value) {
    if (isset($_COOKIE[$key]) && !empty($_COOKIE[$key])) {
        $args['default'] = $_COOKIE[$key];
    }
    return $args;
}, 99, 3);

add_filter('woocommerce_form_field_country', function ($field, $key, $args, $value) {
    if (isset($_COOKIE[$key]) && !empty($_COOKIE[$key])) {
        $field = str_replace($_COOKIE[$key], $_COOKIE[$key] . '" selected="selected', $field);
    }
    return $field;
}, 99, 4);

/* function vari_add_bcc_emails( $headers, $email_id ){
	if( 'cancelled_order' == $email_id || 'new_order' == $email_id || 'customer_completed_order' == $email_id ){
		$headers .= 'BCC: eden.d@variscite.com' . "\r\n";
		$headers .= 'BCC: ayelet.o@variscite.com' . "\r\n";
		$headers .= 'BCC: lena.g@variscite.com' . "\r\n";
	}
    return $headers;
}
add_filter( 'woocommerce_email_headers', 'vari_add_bcc_emails', 10, 2 ); */


function create_custom_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'input_tests';

    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
        $sql = "CREATE TABLE $table_name (
            id INT(11) NOT NULL AUTO_INCREMENT,
            input_data TEXT NOT NULL,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}
add_action('after_setup_theme', 'create_custom_table');

function o_sctipts()
{
    wp_enqueue_script('scripts.js', get_stylesheet_directory_uri() . '/scripts.js', array('jquery'), '1.0');
    wp_localize_script('scripts.js', 'myAjax', array('ajaxurl' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'o_sctipts');
function o_sendPhoneCode()
{
    // Check for nonce security


    // Sanitize the input data and insert it into the database.
    $inputData = sanitize_text_field($_POST['input_data']);

    // Insert the $inputData into the database.
    global $wpdb;
    $table_name = 'hcu_input_tests'; // Change it with your table name
    $user_ip = $_SERVER['REMOTE_ADDR'];
    $date = date("Y/m/d");
    $wpdb->insert($table_name, array(
        'input_data' => $inputData,
        'ip' => $user_ip,
        'date' => $date,
    ));

    //  $wpdb->query( "INSERT INTO {$table_name} ('input_data') VALUES ({$inputData});" );

    $response = array('message' => 'Data saved');

    wp_send_json_success($response);
    die();
}

add_action("wp_ajax_o_sendPhoneCode", "o_sendPhoneCode");
add_action("wp_ajax_nopriv_o_sendPhoneCode", "o_sendPhoneCode");

function redirect_post_lead()
{
    if (is_singular('lead')) :
        wp_redirect(home_url(), 301);
        exit;
    endif;
}
add_action('template_redirect', 'redirect_post_lead');

//skip guest email verification
add_action('woocommerce_order_email_verification_required', function () {
    return false;
});


//datalayer
add_action('woocommerce_thankyou', 'vari_add_payment_datalayer');
function vari_add_payment_datalayer($order_id)
{
    $paypal_capture_res = get_post_meta($order_id, 'paypal_capture_res', true);
    $paypal_capture_res = json_decode($paypal_capture_res);
    if ((isset($paypal_capture_res->intent) && $paypal_capture_res->intent == "CAPTURE" && isset($paypal_capture_res->status) && $paypal_capture_res->status == "COMPLETED") || (isset($_GET['printdatalayer']))) {
        $order = new WC_Order($order_id);

        $epq = get_post_meta($order_id, '_estimated_product_quantities', true);
        $order_items = $order->get_items();
        $pids = array();
        foreach ($order_items as $item_id => $item) {
            $pids[] = $item['product_id'];
        }
    ?>
        <script>
            var items = [];
            <?php
            if ($order_items) {
                foreach ($order_items as $item_id => $item) {
                    $qty = $item->get_quantity();
                    $item_variant = '';
                    $parentProductId = $item['product_id'];
                    if (isset($item['variation_id']) && !empty($item['variation_id'])) {
                        $product_id = $item['variation_id'];
                    } else {
                        $product_id = $item['product_id'];
                    }
                    $_product = wc_get_product($product_id);
                    $sku = $_product->get_sku();
                    if (get_field('pro_index', $parentProductId)) {
                        $pro_index = get_field('pro_index', $parentProductId);
                    } else {
                        $pro_index = '';
                    }

                    $item_type = '';
                    $product_cat = get_the_terms($parentProductId, 'product_cat');
                    if ($product_cat) {
                        foreach ($product_cat as $cat) {
                            $item_type = $cat->name;
                            break;
                        }
                    }

                    $pa_som_title = '';
                    $pa_som = wc_get_order_item_meta($item_id, 'pa_som-configuration', true);
                    $pa_som_term = get_term_by('slug', $pa_som, 'pa_som-configuration');
                    if ($pa_som_term) {
                        $pa_som_title = $pa_som_term->name;
                    }

                    $os = wc_get_order_item_meta($item_id, 'pa_operating-system', true);
                    $pa_kit = wc_get_order_item_meta($item_id, 'pa_kit', true);
                    if (strpos($pa_kit, "development-kit") !== false) {
                        $item_variant = 'Development Kit';
                    } else if (strpos($pa_kit, "starter-kit") !== false) {
                        $item_variant = 'Starter Kit';
                    }

                    $accessories = "no";
                    $accessoriesName = "";

                    if ($_product->get_type() == "variable" || $_product->get_type() == "variation") {
                        $options = array();
                        $terms = wc_get_product_terms($item->get_product_id(), 'pa_kit', array(
                            'fields' => 'all',
                        ));

                        $attributes = $_product->get_variation_attributes();
                        if (isset($attributes['attribute_pa_kit']))
                            $options[] = $attributes['attribute_pa_kit'];

                        $product_accessories = array();
                        foreach ($terms as $term) {
                            if (in_array($term->slug, $options) && in_array($pa_kit, $options)) {
                                $product_accessories = get_field('product_accessories', 'pa_kit_' . $term->term_id);
                            }
                        }

                        if ($pids) {
                            foreach ($pids as $pid) {
                                if (in_array($pid, $product_accessories)) {
                                    $accessories = "yes";
                                    $accessoriesName = get_the_title($pid);
                                }
                            }
                        }
                    }
            ?>
                    items.push({
                        "item_id": "<?php echo $sku; ?>",
                        "item_name": "<?php echo $item['name']; ?>",
                        "item_type": "<?php echo $item_type; ?>",
                        "price": "<?php echo $_product->get_price(); ?>",
                        "quantity": "<?php echo $qty; ?>",
                        "item_variant": "<?php echo $item_variant; ?>",
                        "item_category": "<?php echo $pa_som_title; ?>",
                        "item_category2": "<?php echo $os; ?>",
                        "item_category3": "<?php echo $epq; ?>",
                        "item_category4": "<?php echo $accessories; ?>",
                        "item_category5": "<?php echo $accessoriesName; ?>",
                        "index": <?php echo $pro_index; ?>,
                    });
            <?php }
            } ?>
            dataLayer.push({
                ecommerce: null
            }); // Clear the previous ecommerce object.
            dataLayer.push({
                "event": "purchase",
                "ecommerce": {
                    "currency": "USD",
                    "payment_type": "paypal",
                    "value": <?php echo $order->get_total(); ?>, // סך הכל לתשלום בסל
                    "shipping": <?php echo $order->get_total_shipping(); ?>,
                    "transaction_id": "<?php echo $paypal_capture_res->id; ?>",
                    "coupon": "",
                    "items": items
                }
            });
            console.log(dataLayer);
        </script>
<?php
    }
}

function get_var_id()
{
    $product_id = $_POST['product_id'];
    $pa_kit = $_POST['pa_kit'];
    $pa_som_configuration =  $_POST['pa_som_configuration'];
    $pa_operating_system = $_POST['pa_operating_system'];

    $match_attributes =  array(
        "attribute_pa_kit" => $pa_kit,
        "attribute_pa_som-configuration" => $pa_som_configuration,
        "attribute_pa_operating-system" => $pa_operating_system
    );
    $var_id = find_matching_product_variation_id($product_id, $match_attributes);

    $variation = wc_get_product($var_id);
    $var_price = $variation->price;
    $var_name = $variation->name;
    $var_sku = $variation->sku;
    $curr =  get_woocommerce_currency($var_id);
    if (strpos($var_name, 'Evaluation Kits') !== false) {
        $var_type = 'Evaluation Kits';
    }
    if (strpos($var_sku, 'STK') !== false) {
        $variant = 'Starter Kit';
    } else {
        $variant = 'Development Kit';
    }
    $response = array(
        'name' => $var_name,
        'price' => $var_price,
        'sku' => $var_sku,
        'variant' => $variant,
        'type' => $var_type,
        'curr' => $curr
    );

    wp_send_json_success($response);
    die();
}
add_action("wp_ajax_get_var_id", "get_var_id");
add_action("wp_ajax_nopriv_get_var_id", "get_var_id");

add_filter('wp_mail', function ($args) {
    $fa = fopen(ABSPATH . 'mail-lang-dbg2.log', 'a+');
    fwrite($fa, '-----' . date('Y-m-d H:i:s') . '-----');
    fwrite($fa, PHP_EOL);
    fwrite($fa, print_r($args, true));
    fwrite($fa, PHP_EOL);
    fwrite($fa, PHP_EOL);
    fclose($fa);

    return $args;
});

//send admin emails always in english
add_filter('wcml_get_admin_language_by_email', 'vari_language_for_new_order_admin_email', 10, 3);
function vari_language_for_new_order_admin_email($admin_language, $recipient, $order_id)
{
    return 'en';
}

/* EORI */
add_filter('woocommerce_checkout_fields', 'woocommerce_checkout_fields_add_eori_checkout_filed', 99, 1);
function woocommerce_checkout_fields_add_eori_checkout_filed($fields)
{
    $woo_to_sfdc_eori_countries = get_field('woo_to_sfdc_eori_countries', 'option');
    $woo_to_sfdc_eori_countries_list = explode("\n", $woo_to_sfdc_eori_countries);
    $eori_countries_list_short_country_name = [];

    foreach ($woo_to_sfdc_eori_countries_list as $country) {
        $country_arr = explode(':', $country);
        $eori_countries_list_short_country_name[] = trim($country_arr[0]);
    }
    //    var_dump($fields['billing']);
    $eori_filed = array(
        'type'      => 'text',
        'label'     => __('EORI number', 'woocommerce'),
        'placeholder'   => _x('Add EORI number', 'placeholder', 'woocommerce'),
        'required'  => false,
        'class'     => array('form-row-wide', 'none'),
        'clear'     => true,
        'priority'  => 90,
        'custom_attributes' => ['data-eori_notice' => __('* To prevent delays in customs please provide your EORI number', 'woocommerce')]
    );

    if (!empty($eori_countries_list_short_country_name)) {
        $eori_filed['custom_attributes']['data-eori_countries'] = implode(',', $eori_countries_list_short_country_name);
    }

    $fields['billing']['billing_eori'] = $eori_filed;
    $fields['shipping']['shipping_eori'] = $eori_filed;
    return $fields;
}

add_action('woocommerce_admin_order_data_after_shipping_address', 'woocommerce_admin_order_data_after_shipping_address_add_eori_checkout_filed', 10, 1);
function woocommerce_admin_order_data_after_shipping_address_add_eori_checkout_filed($order)
{
    echo '<p><strong>' . __('EORI number') . ':</strong> ' . get_post_meta($order->get_id(), '_shipping_eori', true) . '</p>';
}

// cart custom text before proceed_to_checkout button
function custom_text_before_checkout_button()
{
    echo '<div class="checkout-notification">' . __("Please notice, orders can't be modified after payment", "variscite") . '</div>';
}
add_action('woocommerce_proceed_to_checkout', 'custom_text_before_checkout_button', 10);
