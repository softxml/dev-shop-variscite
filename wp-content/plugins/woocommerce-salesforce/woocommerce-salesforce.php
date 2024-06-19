<?php
/**
 * Plugin Name: Variscite WooCommerce to Salesforce
 * Version: 0.1
 * Author: Theodore Dominiak
 * License: GPL2
 */

if(! class_exists('SforceSoapClient')) {
    require_once 'soapclient/SforcePartnerClient.php';
}

require_once 'woo-to-sfdc.php';
require_once 'option-pages.php';
require_once 'api-to-lead.php';

$woosfdc = new wooToSFDC_optionsPage();