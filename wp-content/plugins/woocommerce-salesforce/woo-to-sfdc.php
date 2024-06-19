<?php
    class wooToSFDC {

        function __construct() {
            $this->api_to_lead = new wooToSFDC_api_to_lead();
            $this->define_hooks_and_pass_data();
        }

        function define_hooks_and_pass_data() {

            // Remove and update WooCommerce irrelevant countries according to the wp-admin page
            add_action('woocommerce_countries', array($this, 'variscite_custom_woocommerce_countries'), 10, 1);

            // Remove and update WooCommerce states for countries
            add_action('woocommerce_states', array($this, 'variscite_custom_woocommerce_states'), 10, 1);

            // Pass new lead info on order creation
            add_action('woocommerce_checkout_order_processed', array($this->api_to_lead, 'create_new_lead'));

            // Pass 'Payment Approval' on payment approval from PayPal
            // PayPal Express - woocommerce_paypal_express_checkout_valid_ipn_request
            add_action('valid-paypal-standard-ipn-request', array($this->api_to_lead, 'update_lead_data'));
        }

        function variscite_custom_woocommerce_countries($country) {

            $country = array();

            $countries_from_SFDC_list = get_field('woo_to_sfdc_countries', 'option');
            $countries_from_SFDC = explode("\n", $countries_from_SFDC_list);

            foreach($countries_from_SFDC as $the_country) {

                $country_exploded = explode(' : ', $the_country);

                $cc = rtrim($country_exploded[0]);
                $cn = rtrim($country_exploded[1]);

                $country[$cc] = $cn;
            }

            return $country;
        }

        function variscite_custom_woocommerce_states() {

            $states = array();

            $states_from_SFDC_list = get_field('woo_to_sfdc_states', 'option');

            foreach($states_from_SFDC_list as $states_per_countries) {
                $states[$states_per_countries['woo_to_sfdc_states_cc']] = array();

                $this_countries_states_list = $states_per_countries['woo_to_sfdc_states_picklist'];
                $this_countries_states = explode("\n", $this_countries_states_list);

                foreach($this_countries_states as $the_state) {

                    $states_exploded = explode(' : ', $the_state);

                    $stc = rtrim($states_exploded[0]);
                    $stn = rtrim($states_exploded[1]);

                    $states[$states_per_countries['woo_to_sfdc_states_cc']][$stc] = $stn;
                }
            }

            return $states;
        }
    }