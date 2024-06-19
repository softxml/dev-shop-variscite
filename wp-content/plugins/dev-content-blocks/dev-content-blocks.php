<?php
/**
 * Plugin Name: Dev Content Blocks
 * Description: This plugin adds content blocks with HTML, JS, and CSS blocks to be called by using a shortcode.
 * Version: 1.4.1
 * Author: Allon Sacks
 * Author URI: http://www.digitalcontact.co.il
 * License: GPL2
 */
defined( 'ABSPATH' ) or die( 'Time for a U turn!' );
define("DC_DCB_VERSION", "1.4.1");

include "post-type.php";
include "revisions.php";
include "metabox-info.php";
include "metabox-blocks.php";
include "metabox-options.php";
include "shortcode.php";
