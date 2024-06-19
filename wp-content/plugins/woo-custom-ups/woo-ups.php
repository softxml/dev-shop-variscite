<?php
/**
 * Plugin Name: Variscite Custom UPS Integration
 * Version: 0.1
 * Author: Theodore Dominiak
 * License: GPL2
 */

require_once 'ups-shipping-rates.php';
require_once 'woocommerce-shipping.php';
require_once 'options-page.php';

$wooups = new wooUPS_optionsPage();
$ups = new wooUPS();