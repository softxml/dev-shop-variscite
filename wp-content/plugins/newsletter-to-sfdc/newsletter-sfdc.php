<?php
/**
 * Plugin Name: Variscite Newsletter to Salesforce
 * Version: 0.1
 * Author: Theodore Dominiak
 * License: GPL2
 */

if(! class_exists('SforceSoapClient')) {
    require_once 'soapclient/SforcePartnerClient.php';
}

require_once 'shortcode.php';
require_once 'sfdc-integration.php';

new newsletterSFDCIntegrationShortcode();