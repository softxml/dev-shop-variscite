<?php
    class wooToSFDC_optionsPage extends wooToSFDC {

        function __construct() {

            if (function_exists('acf_add_options_page')) {
                $this->sfdc = new wooToSFDC();
                add_action('admin_menu', array($this, 'SFDC_options_page'));
            }
        }

        function SFDC_options_page() {

            acf_add_options_page(array(
                'page_title' => 'Woo to SFDC',
                'menu_title' => 'Woo to SFDC',
                'menu_slug' => 'woo-to-sfdc-settings',
                'capability' => 'edit_posts',
                'redirect' => false,
                'icon_url' => 'dashicons-analytics',
                'position' => 58
            ));
        }
    }