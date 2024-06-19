<?php
/**
 * Plugin Name: Variscite to Salesforce
 * Description: Send data to salesforce
 * Version: 1.0
 * Author: Theodore Dominiak
 * Text Domain: variscite-salesforce
 * License: GPL2
**/

if( ! defined('ABSPATH') ){
    die; // if accessed directly
}

// plugin directory
define( 'VARISCITE_DIR', plugin_dir_path( __FILE__ ) );
define( 'VARISCITE_URL', plugin_dir_url( __FILE__ ) );

require_once( VARISCITE_DIR.'/inc/shortcode.php' );
require_once( VARISCITE_DIR.'/inc/sfdc-integration.php' );
require_once( VARISCITE_DIR.'/inc/woo-to-sfdc.php' );
require_once( VARISCITE_DIR.'/inc/admin-menu.php' );
require_once( VARISCITE_DIR.'/inc/global-ajax.php' );

new newsletterSFDCIntegrationShortcode();
new wooToSFDC_api_to_lead();