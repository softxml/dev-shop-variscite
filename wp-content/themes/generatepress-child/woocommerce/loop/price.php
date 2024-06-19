<?php
/**
 * Loop Price
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/loop/price.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

global $product;
?>

<?php

    if($product->is_type('variable')) {
        $min_price   = $product->get_variation_price('min');
        $max_price   = $product->get_variation_price('max');
        $price_label = __('Starting from:', 'variscite');
        $is_som_cat = has_term('system-on-module', 'product_cat', $product->get_id());

        if(get_field('variscite__product_is_not_purchasable')) {
            $min_price = get_field('variscite__product_custom_price');
        } else {

            if((int) $min_price == (int) $max_price) {
                $price_label = __('Price:', 'variscite');
            }
        }

        if($min_price && ! empty($min_price)) {
//            if (!$is_som_cat) {
                echo '<span class="price">' . $price_label . ' <strong class="amount">' . get_woocommerce_currency_symbol() . $min_price . '</strong></span>';
//            }
        }
//          else {
//            echo '';
//        }

    } else {

        if(get_field('variscite__product_is_not_purchasable')) {
            $price = get_woocommerce_currency_symbol() . get_field('variscite__product_custom_price');
        } else {
            $price = $product->get_price_html();
        }

        if($price && ! empty($price)) {
            echo '<h2 class="price">'.__("Price","variscite").': <strong class="amount">' . $price . '</strong></h2>';
        } else {
            echo '';
        }
    }

    ?>
