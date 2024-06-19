<?php

    class wooUPS {

        private $items;
        private $ups_int;
        private $ups_data;
        private $packages;
        private $kit_mapping;

        function __construct() {
            $this->ups_int = new varisciteUPSShipping();

            $this->items = array(
                'som'       => array(),
                'accessory' => array(
                    'camera-modules' => 0,
                    'power-supplies' => 0
                ),
                'kit'       => array(
                    'sm-starter' => 0,
                    'lg-starter' => 0,
                    'sm-dev'     => 0,
                    'lg-dev'     => 0
                )
            );

            $this->kit_mapping = array(
                'sm-starter' => array(
                    'single' => 10,
                    'multi'  => 11
                ),
                'lg-starter' => array(
                    'single' => 4,
                    'multi'  => 5
                ),
                'sm-dev'     => array(
                    'single' => 9,
                    'multi'  => 12
                ),
                'lg-dev'     => array(
                    'single' => 8,
                    'multi'  => 1
                )
            );

            if(! is_admin()) {
                add_filter('woocommerce_checkout_update_order_review', array($this, 'clear_wc_shipping_rates_cache'));
                add_filter('woocommerce_package_rates', array($this, 'override_woocommerce_shipping_amount'));
            }
        }

        private function get_cart_contents_and_apply_logic() {
            global $woocommerce;
            $items = $woocommerce->cart->get_cart();

            foreach($items as $item_key => $cart_items) {
                $product_id = $cart_items['data']->get_id();
                $_product = wc_get_product($product_id);

                // Get product category and apply relevant action
                $terms = get_the_terms($product_id, 'product_cat');

                // For variation products, get parent product ID
                if(! $terms) {
                    $terms = get_the_terms($_product->get_parent_id(), 'product_cat');
                }

                //$term_names = array_map(create_function('$o', 'return $o->slug;'), $terms);
                $term_names = array_map(function( $o ){ return $o->slug; }, $terms);
                $product_type = in_array('evaluation-kit', $term_names) ? 'kit' : (in_array('system-on-module', $term_names) ? 'som' : 'accessory');

                for($i = 0; $i < $cart_items['quantity']; $i++) {

                    if($product_type == 'kit') {

                        // Get kit tag (small or large)
                        $variation_data = $_product->get_variation_attributes();

                        $selected_kit = $variation_data['attribute_pa_kit'];
                        $kit_id = get_term_by('slug', $selected_kit, 'pa_kit');
                        $kit_size = get_field('variscite_ups_kit_type', 'pa_kit_' . $kit_id->term_id);

                        $this->items[$product_type][$kit_size]++;

                    } else if($product_type == 'accessory') {

                        $ac_type = '';

                        foreach($terms as $term) {

                            if($term->name !== 'Accessories') {
                                $ac_type = $term->slug;
                            }
                        }

                        $this->items[$product_type][$ac_type]++;

                    } else {

                        if($product_type == 'som') {

                            // Divide the SOMs to different packages based on their type
                            $parent_id = $cart_items['product_id'];

                            if(! $this->items[$product_type][$parent_id] || ! isset($this->items[$product_type][$parent_id])) {
                                $this->items[$product_type][$parent_id] = 0;
                            }

                            $this->items[$product_type][$parent_id]++;

                        } else {

                            $this->items[$product_type]++;
                        }
                    }
                }
            }

            return $this->apply_custom_packaging_logic();
        }

        private function apply_custom_packaging_logic() {

            $unique_kits = 0;

            // Init the package for accessories if any are set
            if(! empty($this->items['accessory']['camera-modules']) && ! empty($this->items['accessory']['power-supplies'])) {
                $this->packages[4] = 0;
            }

            // Divide the power supplies into packages
            if($this->items['accessory']['power-supplies'] > 0) {
                $this->packages[4] += ceil($this->items['accessory']['power-supplies'] / 2);
            }

            // Divide the SOMs into packages
            if($this->items['som'] && ! empty($this->items['som'])) {

                // Init the SOM packages
                $this->packages[3] = 0;

                foreach($this->items['som'] as $som) {
                    $this->packages[3] += ceil($som / 20);
                }
            }

            // Divide the kits into packages
            foreach($this->items['kit'] as $type => $qty) {

                if($qty <= 0) {
                    continue;
                }

                // If the number is even, divide into 2 pieces per package
                if($qty % 2 == 0) {
                    $this->packages[$this->kit_mapping[$type]['multi']] = $qty / 2;
                    $unique_kits += $qty / 2;
                } else { // If not:

                    // Get the even number and divide into 2 pieces per package
                    if($qty > 1) {
                        $this->packages[$this->kit_mapping[$type]['multi']] = floor($qty / 2);
                        $unique_kits += floor($qty / 2);
                    }

                    // Put the last odd item into a separate package
                    $this->packages[$this->kit_mapping[$type]['single']] = 1;
                    $unique_kits++;
                }
            }

            // Remove the cameras according to the number of Kits
            $this->items['accessory']['camera-modules'] -= $unique_kits;

            // Divide the leftover cameras into packages
            if($this->items['accessory']['camera-modules'] > 0) {
                $this->packages[4] += ceil($this->items['accessory']['camera-modules'] / 5);
            }

            // Go over the packages and unset the empty ones
            foreach($this->packages as $key => $package) {

                if($package == 0) {
                    unset($this->packages[$key]);
                }
            }

            return $this->packages;
        }

        public function override_woocommerce_shipping_amount($rates) {

            // Apply discount if exists to the rate
            $shipping_zone = $this->get_current_shipping_zone_id();
            $shipping_rate = $this->apply_zone_discount_on_shipping($shipping_zone, $this->get_ups_shipping_data());

            WC()->session->set('calculated_rate', $shipping_rate);

            foreach($rates as $rate) {

                if($rate->get_method_id() == 'flat_rate' && strpos($rate->get_label(), 'UPS') !== false) {
                    $rate->set_cost($shipping_rate);
                }
            }

            return $rates;
        }

        private function apply_zone_discount_on_shipping($zone, $rate) {

            $order_discount_log = 'Initial shipping sum: ' . $rate . "\n";

            $discounted_zones = get_field('woo_ups__shipping_zones', 'option');
            $current_zone = false;

            foreach($discounted_zones as $d_zone) {

                if($d_zone['woo_ups_shipping_zones__zone'] == $zone) {

                    $discount = $d_zone['woo_ups_shipping_zones__price_adj'];
                    $order_discount_log .= 'Shipping zone matches: ' . $zone . "\n";

                    if((int)$discount < 0) {
                        $discount = str_replace('-','', $discount);
                        $rate -= $rate * ($discount / 100);

                        $order_discount_log .= 'Discount applied for the sum of: ' . ($rate * ($discount / 100)) . ' (' . $discount . '%)' . "\n";

                    } else {
                        $rate += $rate * ($discount / 100);

                        $order_discount_log .= 'Additional charge applied for the sum of: ' . ($rate * ($discount / 100)) . ' (' . $discount . '%)' . "\n";
                    }
                }
            }

            // Add 20% discount for shipping rated over 120USD
            if($rate > 120) {
                $order_discount_log .= 'Shipping is above 120USD, (' . ($rate) . ') adding 20% discount: ' . ($rate * 0.2) . "\n";
                $rate = $rate * 0.8;
            } else if($rate < 120) {
                $order_discount_log .= 'Shipping is below 120USD, (' . ($rate) . ') adding 10% discount: ' . ($rate * 0.1) . "\n";
                $rate = $rate * 0.9;
            }

            WC()->session->set('calculated_shipping_discount', $order_discount_log);

            return number_format((float)$rate, 2, '.', ',');
        }

        private function get_current_shipping_zone_id() {
            $customer_country = WC()->session->get('customer')['shipping_country'];

            $delivery_zones = WC_Shipping_Zones::get_zones();
            $current_zone_id = false;

            foreach($delivery_zones as $zone) {

                foreach($zone['zone_locations'] as $country) {

                    if($customer_country == $country->code) {
                        return $zone['id'];
                    }
                }
            }
        }

        private function get_ups_shipping_data() {
            $this->ups_data['packages'] = $this->get_cart_contents_and_apply_logic();
            $shipping_data = $this->get_customer_shipping_data();

            $rates = $this->ups_int->get_shipping_rates($this->ups_data, $shipping_data);

            // Store the data in the current user's session to be later saved in the log
            WC()->session->set('ups_packages', json_encode($this->ups_data));
            WC()->session->set('ups_location', json_encode($shipping_data));

            return $rates;
        }

        private function get_customer_shipping_data() {

            global $woocommerce;

            // Get customer address
            $postcode = WC()->session->get('customer')['shipping_postcode'];
            $company = WC()->session->get('customer')['company'];

            $customer_address = array(
                'address'   => implode('', array(WC()->session->get('customer')['shipping_address_1'], WC()->session->get('customer')['shipping_address_2'])),
                'city'      => WC()->session->get('customer')['shipping_city'],
                'country'   => WC()->session->get('customer')['shipping_country']
            );

            // Get store address
            $store_address     = get_option('woocommerce_store_address');
            $store_address_2   = get_option('woocommerce_store_address_2');
            $store_city        = get_option('woocommerce_store_city');
            $store_postcode    = get_option('woocommerce_store_postcode');

            // The country/state
            $store_raw_country = get_option('woocommerce_default_country');
            $split_country = explode( ":", $store_raw_country );
            $store_country = $split_country[0];

            $store_address = array(
                'address'   => implode(' ', array($store_address, $store_address_2)),
                'city'      => $store_city,
                'country'   => $store_country
            );

            $data_arr = array(
                'to' => array(
                    'company' => $company,
                    'zip' => $postcode,
                    'addr' => $customer_address
                ),
                'from' => array(
                    'zip' => $store_postcode,
                    'addr' => $store_address
                )
            );

            return $data_arr;
        }

        public function clear_wc_shipping_rates_cache() {
            $packages = WC()->cart->get_shipping_packages();

            foreach ($packages as $key => $value) {
                $shipping_session = "shipping_for_package_$key";

                unset(WC()->session->$shipping_session);
            }
        }
    }