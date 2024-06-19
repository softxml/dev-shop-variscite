<?php
/**
 * Single Product Price
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/price.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @author  WooThemes
 * @package WooCommerce/Templates
 * @version 3.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $product;
$is_som_cat = has_term('system-on-module', 'product_cat', $product->get_id());
// (@AsafA) return price
$price_html = '';
if($product->is_type('variable')) {
    $min_price = $product->get_variation_price('min');
    $max_price = $product->get_variation_price('max');
    $price_label = __('Starting from:', 'variscite');
    if (get_field('variscite__product_is_not_purchasable')) {
        $min_price = get_field('variscite__product_custom_price');
    } else {
        if ((int)$min_price == (int)$max_price) {
            $price_label = __('Price:', 'variscite');
        }
    }
    if ($min_price && !empty($min_price)) {
        $price_html = '<div class="price">' . $price_label . ' <strong class="amount">' . get_woocommerce_currency_symbol() . $min_price . '</strong></div>';
    }
} else {
    if(get_field('variscite__product_is_not_purchasable')) {
        $price = get_woocommerce_currency_symbol() . get_field('variscite__product_custom_price');
    } else {
        $price = $product->get_price_html();
    }
    if($price && ! empty($price)) {
        $price_html = '<div class="price">'.__("Price", "variscite").': <strong class="amount">' . $price . '</strong></div>';
    }
}
?>
<?php echo $price_html; ?>
<?php
if (!$is_som_cat) {
    $ships_time = get_field('shipping_within_time');
    if( $ships_time ){
        echo '<div class="ships_info">'. $ships_time .'</div>';
    }
}
else{
    // use for css margin
    echo '<div class="ships_info"></div>';  
}
?>
