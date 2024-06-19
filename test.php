<?php
//define( 'WP_DEBUG', true );
require_once( 'wp-config.php' );

$sku = 'VAR-DVK-VS8M-PLUS_V2_LO';
global $wpdb;
$product_id = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."postmeta WHERE post_id = 21431" );
$product_id = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $sku ) );

//$product = wc_get_product( 21431 );

$data = [ 'meta_value' => $sku ]; // NULL value.
$where = [ 'meta_id' => 591295 ]; // NULL value in WHERE clause.

//$a = $wpdb->update( $wpdb->prefix . 'postmeta', $data, $where );
echo '<pre>';
print_r( $product_id );