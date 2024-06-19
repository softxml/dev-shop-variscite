<?php

    class wooUPS_optionsPage extends wooUPS {

        function __construct() {

            if (function_exists('acf_add_options_page')) {
                add_action('admin_menu', array($this, 'UPS_options_page'));
            }

            // Prefill the shipping zones field with the ones set up in WooCommerce
            add_filter('acf/load_field/name=woo_ups_shipping_zones__zone', array($this, 'prefill_shipping_zones_for_woo'));
        }

        function UPS_options_page() {

            acf_add_options_page(array(
                'page_title' => 'Variscite UPS',
                'menu_title' => 'Variscite UPS',
                'menu_slug' => 'variscite-ups-settings',
                'capability' => 'edit_posts',
                'redirect' => false,
                'position' => 58,
                'icon_url' => 'dashicons-archive'
            ));
        }

        public function prefill_shipping_zones_for_woo($field) {
            $field['choices'] = array();

            $delivery_zones = WC_Shipping_Zones::get_zones();

            foreach($delivery_zones as $zone) {
                $field['choices'][$zone['id']] = $zone['zone_name'];
            }

            return $field;
        }
    }