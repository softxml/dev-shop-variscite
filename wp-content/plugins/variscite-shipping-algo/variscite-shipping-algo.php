<?php
/**
 * Plugin Name: Variscite Shipping Algorithm
 * Version: 0.0.1
 * Author: Theodore Dominiak
 * License: GPL2
 */

// Require the admin scripts
require_once __DIR__ . '/admin/option-page.php';
require_once __DIR__ . '/classes/class.ups-api.php';

// The main plugin class
class variShippingAlgo {

    public $config;
    private $kgs;
    private $zone;
    private $freight;
    private $total;
	public $s_country;
	
    function __construct() {

        // Setup the config from the ACF options page and organize it in a
        // more comfortable way.
        $this->config = $this->organize_config(
            get_global_option( 'vari__algo_products-config')
        );

        if ( ! is_admin() ) {
            // Get the user's current shipping zone config
            $this->get_shipping_zone_config();

            // Set the KGs param to 0.
            $this->kgs = 0;

            // Set the total param to 0.
            $this->total = 0;
        }
		
		if( isset( $_POST['post_data'] ) && !empty( $_POST['post_data'] ) ){
			parse_str( $_POST['post_data'], $outputArray );
			if( isset( $outputArray['shipping_country'] ) && !empty( $outputArray['shipping_country'] ) ){
				$this->s_country = $outputArray['shipping_country'];
			}else if( isset( $outputArray['billing_country'] ) && !empty( $outputArray['billing_country'] ) ){
				$this->s_country = $outputArray['billing_country'];
			}
		}
    }

    public function get_total() {
        return $this->total;
    }

    // The main function - calculate the shipping
    // rate based on the user's cart contents.
    public function calculate_shipping_rate() {

        // Get the user's cart
        $cart = WC()->cart->get_cart();

        if($cart && ! empty($cart)) {

            $weights = array();

            // First, calculate the total weight of the whole cart's items.
            $checked_soms = array();
            foreach($cart as $hash => $item) {
                $type = $this->get_cart_item_product_type($item);

                // If the current item is SOM, get the name
                if ($type == "soms") {
                    $sku = explode(" ", $item['data']->get_title())[0];

                    // SOMs variations of the same variable product are
                    // calculated as one unit regardless of their quantity.
                    // Hence, we want to skip the iteration if a variable product of
                    // a SOM variation has already been checked.
                    if (in_array($sku, $checked_soms)) {
                        continue;
                    }

                    $checked_soms[] = $sku;
                    $qty = 1;

                    // If the current item is KIT, get the `kit` attribute
                } else if ($type == "kits") {
                    $product = wc_get_product($item['data']->get_id());
                    $sku = get_term_by('slug', $product->get_attributes()["pa_kit"], 'pa_kit')->name;
                    $qty  = $item['quantity'];

                    // Else, use the SKU
                } else if ( $type == "accessories" ){
                    $product = wc_get_product( $item['product_id'] );
                    $sku = $product->get_title();
					if( in_array( $sku, $checked_soms ) ){
                        continue;
                    }
                    $checked_soms[] = $sku;
                    $qty  = $item['quantity'];
                } else if ( $type == "carrier_board" ) {
                    $product = wc_get_product($item['data']->get_id());
                    $sku = get_term_by('slug', $product->get_attributes()["pa_kit"], 'pa_kit')->name;
                    $qty  = $item['quantity'];

                    // Else, use the SKU
                } else {
                    $sku  = $this->get_sku($item);
                    $qty  = $item['quantity'];
                }

//var_dump($type);
//var_dump($sku);
//var_dump($qty);exit;

                // Append the calculated weight to the KGs variable.
				if( $type == "carrier_board" ){
					$ptitle = $item['data']->get_sku();
					$weights[] = $this->config[$type][$ptitle][$qty];
					$this->kgs += $this->config[$type][$ptitle][$qty];
				}else{
					$weights[] = $this->config[$type][$sku][$qty];
					$this->kgs += $this->config[$type][$sku][$qty];
				}
            }
        }

        WC()->session->set("weights", json_encode($weights));
        WC()->session->set("package_weight", $this->kgs);
		
		if( $this->kgs > 10 && is_float( $this->kgs ) ){
			$this->kgs = round( $this->kgs );
		}
		WC()->session->set( 'weight_fix', $this->kgs );
		
//	    echo "init. " . var_dump($this->total) . "<br/>";

        // Get the user's address and identify the shipping zone based on it.
        $this->total = $this->get_freight_cost();

//	    echo "get_freight_cost. " . var_dump($this->total) . "<br/>";

        // Calculate the fuel cost and append to the total
        $this->total += $this->calculate_fuel_cost();

//	    echo "calculate_fuel_cost. " . var_dump($this->total) . "<br/>";

        // Calculate the insurance cost and append to the total
        $this->total += $this->calculate_insurance_cost();

//	    echo "calculate_insurance_cost. " . var_dump($this->total) . "<br/>";

        // Calculate the Reshimon cost and append to the total
        $this->total += $this->get_reshimon_value();

//	    echo "get_reshimon_value. " . var_dump($this->total) . "<br/>";

        // If the area is a remote area (determined by the UPS API),
        // add the remote area surcharge to the total.
        $this->total += $this->add_remote_area_surcharge();

//	    echo "add_remote_area_surcharge. " . var_dump($this->total) . "<br/>";

        // Multiply the current total by the freight multiplier
        $this->multiply_total_by_freight_multiplier();

//	    echo "multiply_total_by_freight_multiplier. " . var_dump($this->total) . "<br/>";

        /*
         *
         * O - order total
         * F - Freight
         * F - Fuel
         * I - Insurance
         * R - Reshimon
         *
         */
//	    echo $this->total . "<br/>";
//	    echo ceil($this->total);
//		exit;
        $this->total = ceil($this->total);
//        echo "ceil. " . var_dump($this->total) . "<br/>";
        WC()->session->set('offir', $this->total);

        // Add safety margin gap to the shipping cost
        $this->total += $this->calculate_safety_margin();
//        echo "calculate_safety_margin. " . var_dump($this->total) . "<br/>";
        WC()->session->set('shipping_with_safety_margin', $this->total);

        // And lastly, set the discount if available for the shipping zone
        if( isset( $this->zone['discount'] ) && $this->zone['discount'] !== 0 ){

            $discount = (float)$this->zone['discount'] / 100;

            WC()->session->set("discount", $discount);

            // If a positive discount, subtract it
            if($discount > 0) {
                $this->total -= $this->total * $discount;
            } else { // Otherwise, add it as surcharge
                $this->total += $this->total * $discount;
            }

        }
		
		$checkForZero = $this->calculate_total_shipping_price();
		if( $checkForZero ){
			$this->total = 0;
		}
		
//        echo "Return. " . var_dump(ceil($this->total)) . "<br/>";

        // And return the sum calculated after all of this flow.
        return ceil($this->total);
    }
	
	//Calculate total shipping
	public function calculate_total_shipping_price(){
		$fuel_multiplier = $this->get_field_with_validation( 'vari__algo_zones-costs--fuel' );
		$insurance_cost = $this->get_field_with_validation( 'vari__algo_zones-costs--insurance' );
		$insurance_min = $this->get_field_with_validation( 'vari__algo_zones-costs--insurance-min' );
		$reshimon = $this->get_field_with_validation( 'vari__algo_zones-costs--reshimon' );
		$freight_multi = $this->get_field_with_validation( 'vari__algo_zones-costs--freight-multi' );
		$remote_multiplier = $this->get_field_with_validation( 'vari__algo_zones-costs--remote-multiplier' );
		$remote_min = $this->get_field_with_validation( 'vari__algo_zones-costs--remote-min' );
		$safety_margin = $this->get_field_with_validation( 'vari__algo_zones-costs--safety_margin' );
		
		if( $fuel_multiplier == 0 || $insurance_cost == 0 || $insurance_min == 0 || $reshimon == 0 || $freight_multi == 0 || $remote_multiplier == 0 || $remote_min == 0 || $safety_margin == 0 ){
			return true;
		}
		return false;
	}
	
    // Calculate the fuel cost based on the freight cost
    private function calculate_fuel_cost() {
        $fuel_multiplier = $this->get_field_with_validation('vari__algo_zones-costs--fuel');
        WC()->session->set('fuel_cost', $this->freight * $fuel_multiplier);
		
		$fa = fopen( ABSPATH."shipping-fuel-dbg.log", "a+" );
		fwrite( $fa, "-----".date( "Y-m-d H:i:s" )."-----" );
		fwrite( $fa, PHP_EOL );
		fwrite( $fa, print_r( $this->freight, true ) );
		fwrite( $fa, PHP_EOL );
		fwrite( $fa, print_r( $fuel_multiplier, true ) );
		fwrite( $fa, PHP_EOL );
		fwrite( $fa, PHP_EOL );
		fclose( $fa );
		
        return $this->freight * $fuel_multiplier;
    }

    // Get the Reshimon cost param
    private function get_reshimon_value() {
        $reshimon = $this->get_field_with_validation('vari__algo_zones-costs--reshimon');
        WC()->session->set('reshimon', $reshimon);
        return $reshimon;
    }

    // Multiply the total by the freight multiplier set in the option page
    private function multiply_total_by_freight_multiplier() {
        $freight_multiplier = (float) $this->get_field_with_validation('vari__algo_zones-costs--freight-multi');
        $this->total = $freight_multiplier * $this->total;
    }

    // Add a remote area surcharge if the address
    // was detected as a remote area by the UPS API.
    private function add_remote_area_surcharge() {
        $ups = new variShippingAlgoUPS();
        $is_remote = $ups->check_for_remote_area();

        // If the area is remote, perform the remote area surcharge calculation.
        if($is_remote) {
            WC()->session->set('is_remote_location', true);
            $multiplier = $this->get_field_with_validation('vari__algo_zones-costs--remote-multiplier');
            $surcharge  = $this->kgs * $multiplier;

            // If the surcharge is lower than the min amount,
            // return the min amount.
            if($surcharge < ($min = $this->get_field_with_validation('vari__algo_zones-costs--remote-min'))) {
                return $min;
            }

            // Otherwise, return the surcharge calculated above
            return $surcharge;
        }

        // Otherwise, return 0 (no surcharge).
        return 0;
    }

    // Calculate the insurance cost
    private function calculate_insurance_cost() {

        // First, get the cart's total
        $total = floatval(WC()->cart->total);

        // Then, get the multiplier value from the options page
        $multi = $this->get_field_with_validation('vari__algo_zones-costs--insurance');

        // Multiply the total by the multiplier
        $total = $total * $multi;

        // Get the min value from the options page
        $min   = $this->get_field_with_validation('vari__algo_zones-costs--insurance-min');

        // If the value is lower, return the min value.
        // Otherwise, return the calculated one.
        if($total < $min) {
            WC()->session->set('insur', $min);
            return $min;
        }

        WC()->session->set('insur', $total);

        return $total;
    }

    // Validate none of the parameters are 0.
    private function get_field_with_validation( $field_name ) {

        // Get the field
        $field_value = get_global_option( $field_name );

        // If the shipping is to Israel,it should return what it finds .
//        if ( WC()->session->get('customer')['shipping_country'] == 'IL' ) {
        if( ( isset( WC()->session->get('customer')['shipping_country'] ) && WC()->session->get('customer')['shipping_country'] == 'IL' ) || ( isset( $this->s_country ) && $this->s_country == 'IL' ) ){
            return $field_value;
        }

        // There is no need to keep searching for a value if a value is found.
        if ( $field_value && $field_value != 0 ) {
            WC()->session->set('exceeded_tries', $this->has_exceeded_tries());
            return $field_value;
        }

        // This was the first try to find a non-zero value.
        if ( !isset($this->tries[$field_name]) ) {
            $this->tries[$field_name] = 1;
        }

        // As long as the number of tries to find a non-zero value is lower than max tries,
        // then try to find a non-zero value
        while ( $this->tries[$field_name] < $this->max_tries ) {
            $field_value = floatval(get_global_option( $field_name ));

            // There is no need to keep searching for a value if a value is found.
            if ( $field_value && $field_value != 0 ) {
                return $field_value;
            }

            // If a value was not found, increment the tries counter.
            $this->tries[$field_name]++;
        }

        // If a non-zero value was not found within max-tries, add a notice to the checkout.
        WC()->session->set('exceeded_tries', $this->has_exceeded_tries());
        $field_obj = get_field_object($field_name, 'option');
        WC()->session->set('exceeded_field', get_field_object($field_name, 'option')['label']);

        return 0;
    }

    private function has_exceeded_tries() {
		if( isset( $this->tries ) && !empty( $this->tries ) ){
			foreach( $this->tries as $field_name => $tries ) {
				if ( $tries >= $this->max_tries ) {
					return true;
				}
			}
		}
        return false;
    }

    public function non_zero_value_found( $error ) {
        if( 'The generic error message' == $error ) {
            $error = 'The shiny brand new error message';
        }
        return $error;
    }

    private function calculate_safety_margin() {

        // Get cost safety margin, default 0
        $safety_margin = floatval($this->get_field_with_validation('vari__algo_zones-costs--safety_margin'));
        WC()->session->set('safety_margin', $safety_margin);

//        if (WC()->session->get('customer')['shipping_country'] == 'IL') {
		if( ( isset( WC()->session->get('customer')['shipping_country'] ) && WC()->session->get('customer')['shipping_country'] == 'IL' ) || ( isset( $this->s_country ) && $this->s_country == 'IL' ) ){
            $cart_total = floatval(WC()->cart->cart_contents_total);
            $cart_total_with_safety_margin = $cart_total * (1 + $safety_margin);
            $handling_fee = $cart_total_with_safety_margin - $cart_total;
            $vat = $cart_total_with_safety_margin * 1.17 - $cart_total_with_safety_margin;

            WC()->cart->set_total_tax(strval($vat));

            //WC()->cart->add_fee('Handling fee', $handling_fee, true);
            WC()->session->set('added_safety_margin', $handling_fee);

            return 0;
        } else {
            $order_total_with_shipping = WC()->cart->cart_contents_total + $this->total;
            WC()->session->set('without_safety_margin', $order_total_with_shipping);
            $added_safety_margin = $order_total_with_shipping * (1 + $safety_margin ) - $order_total_with_shipping;

            WC()->session->set('added_safety_margin', $added_safety_margin);

            return $added_safety_margin;
        }
    }

    // Extract a cart item's SKU
    private function get_sku($item) {
        $product_id = $item['data']->get_id();
        $_product = wc_get_product($product_id);

        return explode('_', $_product->get_sku())[0];
    }

    // Get a cart item's product type (Kit, SOM or Accessory)
    // based on its' product category.
    private function get_cart_item_product_type($item) {
        $product_id = $item['data']->get_id();
        $_product = wc_get_product($product_id);

        // Get product category and apply relevant action
        $terms = get_the_terms($product_id, 'product_cat');

        // For variation products, get parent product ID
        if(! $terms) {
            $terms = get_the_terms($_product->get_parent_id(), 'product_cat');
        }

        $term_names = array_map(function( $o ){ return $o->slug; }, $terms);

        if( in_array( 'evaluation-kit', $term_names ) ){
			$_product_type = 'kits';
		}else if( in_array( 'system-on-module', $term_names ) ){
			$_product_type = 'soms';
		}else if( in_array( 'carrier-board', $term_names ) ){
			$_product_type = 'carrier_board';
		}else{
			$_product_type = 'accessories';
		}
		
		return $_product_type;
    }

    // Get the freight price in a zone based on the weight calculated.
    private function get_freight_cost() {
		if( isset( $this->zone['costs'] ) ){
			$this->zone['costs'] = (array)$this->zone['costs'];
			foreach(array_keys($this->zone['costs']) as $key => $weight) {
				if(
					(float) $this->kgs === (float) $weight ||
					(
						isset(array_keys($this->zone['costs'])[$key + 1]) &&
						(float) $this->kgs > (float) array_keys($this->zone['costs'])[$key + 1] &&
						(float) $this->kgs < (float) array_keys($this->zone['costs'])[$key + 1]
					)
				) {
					if( !isset( $this->zone['costs'][$weight] ) ){
						$weight = round( $weight );
					}
					
					$this->freight = $this->zone['costs'][$weight];
				}
			}
			
			$fa = fopen( ABSPATH."shipping-freight-dbg.log", "a+" );
			fwrite( $fa, "-----".date( "Y-m-d H:i:s" )."-----" );
			fwrite( $fa, PHP_EOL );
			fwrite( $fa, print_r( $this->zone, true ) );
			fwrite( $fa, PHP_EOL );
			fwrite( $fa, print_r( $this->kgs, true ) );
			fwrite( $fa, PHP_EOL );
			fwrite( $fa, print_r( $this->freight, true ) );
			fwrite( $fa, PHP_EOL );
			fwrite( $fa, PHP_EOL );
			fclose( $fa );
			
			WC()->session->set( 'weight', $weight );
			WC()->session->set( 'freight_cost', $this->freight );
		}
        return $this->freight;
    }

    // Get the freight config for the user's current shipping zone
    // based on the shipping country he chose.
    public function get_shipping_zone_config() {

        // Check what zone the customer belongs to
        if(($zone = $this->get_shipping_zone()) !== false) {

            // Get all ACF zones
            $zones = get_global_option('vari__algo_zones-config');

//            echo "Omer<br/>";
//            echo $zone;
//            exit;

            $this->zone = array(
                'costs'    => array(),
                'discount' => 0
            );

            // Iterate through the zones until you find the customer's zone
            foreach($zones as $z) {

                if($z['zone_name'] == $zone) {

                    WC()->session->set('customer_zone', $z['zone_name']);

                    foreach($z['freight_costs'] as $row) {
                        $this->zone['costs'][$row['the_weight']] = $row['the_cost'];
                    }

                    $this->zone['discount'] = $z['the_discount'];
                }
            }

            // If no zone was found, use the 'Rest of the World' zone instead
            if(empty($this->zone)) {
                WC()->session->set('customer_zone', "Rest of the World");
                $other                  = get_global_option('vari__algo_zones-config--other');
                $this->zone['discount'] = $other['the_discount'];

                foreach($other['freight_costs'] as $row) {
                    $this->zone[$row['the_weight']] = $row['the_cost'];
                }
            }
        }
    }

    // Get the user's current shipping zone's name
    private function get_shipping_zone() {
		$customer_country = '';
        $customer = WC()->session->get('customer');
		if( isset( $customer['shipping_country'] ) && !empty( $customer['shipping_country'] ) ){
			$customer_country = $customer['shipping_country'];
		}else if( isset( $customer['billing_country'] ) && !empty( $customer['billing_country'] ) ){
			$customer_country = $customer['billing_country'];
		}
		
		$fa = fopen( ABSPATH."shipping-zone-dbg.log", "a+" );
		fwrite( $fa, "-----".date( "Y-m-d H:i:s" )."-----" );
		fwrite( $fa, PHP_EOL );
		fwrite( $fa, print_r( $customer, true ) );
		fwrite( $fa, PHP_EOL );
		fwrite( $fa, print_r( $customer_country, true ) );
		fwrite( $fa, PHP_EOL );
				
		if( !empty( $customer_country ) ){
			$delivery_zones   = WC_Shipping_Zones::get_zones();
			
			fwrite( $fa, print_r( $delivery_zones, true ) );
			fwrite( $fa, PHP_EOL );
			fwrite( $fa, PHP_EOL );
			fclose( $fa );
			
			foreach( $delivery_zones as $zone ){
				foreach( $zone['zone_locations'] as $country ){
					if( $customer_country == $country->code ){
						return $zone['zone_name'];
					}
				}
			}
		}else{
			fwrite( $fa, PHP_EOL );
			fclose( $fa );
		}
        return false;
    }

    // Organize the config in a [type] => [[amount] => [weight]] array.
    private function organize_config($config) {
        $return = array();

        foreach($config as $_type => $contents) {

            if(empty($_type)) {
                continue;
            }

            $type = str_replace('vari__algo_products-config--', '', $_type);
            $keys = array();

            foreach($contents['header'] as $row) {
                $keys[] = $row['c'];
            }

            foreach($contents['body'] as $row) {

                foreach($row as $key => $cell) {
                    $return[$type][trim($row[0]['c'])][$keys[$key]] = trim($cell['c']);
                }
            }
        }

        return $return;
    }

    public function check_if_product_in_algo_table() {

        // Check if this is a product edit page
        if (( isset($_GET['action']) && $_GET['action'] == 'edit' ) && isset($_GET['post']) ) {
            $product_id = $_GET['post'];
            $_product = wc_get_product( $product_id );
            if ( ! $_product ) {
                return;
            }

            // Get product category and apply relevant action
            $terms = get_the_terms($product_id, 'product_cat');

            // For variation products, get parent product ID
            if(! $terms) {
                $terms = get_the_terms($_product->get_parent_id(), 'product_cat');
            }

            $term_names = array_map(function( $o ){ return $o->slug; }, $terms);

            if( in_array( 'evaluation-kit', $term_names ) ){
				$_product_type = 'kits';
			}else if( in_array( 'system-on-module', $term_names ) ){
				$_product_type = 'soms';
			}else if( in_array( 'carrier-board', $term_names ) ){
				$_product_type = 'carrier_board';
			}else{
				$_product_type = 'accessories';
			}

            // If the current item is SOM, get the name
            if ($_product_type == "soms") {
                $sku = explode(" ", $_product->get_title())[0];
                $this->display_error_on_product_edit_page( $_product_type, $sku );

                // If the current item is KIT, get the `kit` attribute
            } else if ($_product_type == "kits") {
                foreach ($_product->get_attributes()["pa_kit"]["options"] as $attribute_id) {
                    $sku = get_term( $attribute_id )->name;
                    $this->display_error_on_product_edit_page( $_product_type, $sku );
                }

                // Else, use the SKU
            } else if( $_product_type == "carrier_board" ){
				$sku = $_product->get_sku();
                $this->display_error_on_product_edit_page( $_product_type, $sku );
                // Else, use the SKU
            } else {
				if( $_product_type == "accessories" ){
					$ptitle = $_product->get_title();
					$this->display_error_on_product_edit_page( $_product_type, $ptitle );
				}else{
					if ( $_product->is_type( 'simple' ) ) {
						$sku = $this->get_sku(array('data' => $_product));
						$this->display_error_on_product_edit_page( $_product_type, $sku );
					} else if ( $_product->is_type( 'variable' ) ) {
						$variations = $_product->get_available_variations();
						foreach ( $variations as $key => $variation ) {
							$sku = $variation['sku'];
							$this->display_error_on_product_edit_page( $_product_type, $sku );
						}
					}
				}
            }
        }
    }

    public function check_product_in_shop_algo_table( $passed, $product_id, $quantity, $variation_id='', $variation='' )
    {
        if( has_term( 'evaluation-kit', 'product_cat', $product_id ) ){
            $variation = wc_get_product( $variation_id );
			if( $variation ){
				$pa_kit_term = $variation->get_variation_attributes()['attribute_pa_kit'];
				$sku = get_term_by( 'slug', $pa_kit_term, 'pa_kit' )->name;
				if ( !$this->config['kits'][$sku] ) {
					return false;
				}
			}else{
				return false;
			}
        } else if ( has_term( 'system-on-module', 'product_cat', $product_id ) ) {
            $product = wc_get_product( $product_id );
            $sku = explode(" ", $product->get_title())[0];
            if ( !$this->config['soms'][$sku] ) {
                return false;
            }
        } else if( has_term( 'carrier-board', 'product_cat', $product_id ) ){
			if( !empty( $variation_id ) ){
				$product = wc_get_product( $variation_id );
			}else{
				$product = wc_get_product( $product_id );
			}
           
			if( $product ){
				$sku = $product->get_sku();
				if( !$this->config['carrier_board'][$sku] ) {
					return false;
				}
			}
        } else {
            $product = wc_get_product( $product_id );
			$ptitle = $product->get_title();
            /* if ( $product->is_type( 'simple' ) ) {
                $sku = $this->get_sku(array('data' => $product));
            } else if ( $product->is_type( 'variable' ) ) {
                $variation = wc_get_product( $variation_id );
                $sku = $this->get_sku(array('data' => $variation));
            } */
            if ( !$this->config['accessories'][$ptitle] ) {
                return false;
            }
        }
        return true;

    }

    private function display_error_on_product_edit_page( $type, $attribute_name ) {
        if ( !$this->config[$type][$attribute_name] ) {
            echo '<div class="notice notice-error">
            <h2>'.$attribute_name.' was not found in the shop algorithm\'s table</h2>
            </div>';
        }
    }

    public function verify_table_skus() {
        if ( isset($_GET['page']) && $_GET['page'] == 'variscite-shipping-algo' ) {

            global $wpdb;

            // Check if all kits in table has corresponding products
            foreach ( $this->config["kits"] as $kit ) {
                $products = new WP_Query( array(
                    'post_type'         => array('product'),
                    'post_status'       => 'publish',
                    'posts_per_page'    => -1,
                    'tax_query'         => array(
                        array(
                            'taxonomy'      => 'product_cat',
                            'field'         => 'slug',
                            'terms'         => 'evaluation-kit',
                            'operator'      => 'IN'
                        ),
                        array(
                            'taxonomy'      => 'pa_kit',
                            'field'         => 'slug',
                            'terms'         => $kit['SKU'],
                            'operator'      => 'IN'
                        ),
                    )
                ) );
                if ( !$products->have_posts() ) {
                    echo '<div class="notice notice-error">
                    <h2>A kit with SKU "'.$kit['SKU'].'" was not found...</h2>
                    </div>';
                }
            }

            $som_titles = array();
            $products = new WP_Query( array(
                'post_type'         => array('product'),
                'post_status'       => 'publish',
                'posts_per_page'    => -1,
                'tax_query'         => array(
                    array(
                        'taxonomy'      => 'product_cat',
                        'field'         => 'slug',
                        'terms'         => 'system-on-module',
                        'operator'      => 'IN'
                    )
                )
            ) );
            while ( $products->have_posts() ) {
                $products->the_post();
                $som_titles[] = get_the_title();
            }

            // Check if all SOMs in table has corresponding products
            foreach ( $this->config["soms"] as $som ) {
                $should_display_error = true;
                foreach ( $som_titles as $som_title ) {
                    if ( strpos( $som_title, $som['SKU'] ) !== false ) {
                        $should_display_error = false;
                        break;
                    }
                }
                if ( $should_display_error ) {
                    echo '<div class="notice notice-error">
                    <h2>A SOM with SKU "'.$som['SKU'].'" was not found...</h2>
                    </div>';
                }
            }

            // Check if all accessories in table has corresponding products
            /* foreach ( $this->config["accessories"] as $accessory ) {
                if ( !wc_get_product_id_by_sku( $accessory['SKU'] ) ) {
                    echo '<div class="notice notice-error">
                    <h2>An accessory with SKU "'.$accessory['SKU'].'" was not found...</h2>
                    </div>';
                }
            } */

        }
    }
}

// Run the class on the default Woo shipping calculation hook,
// and also clear the Woo shipping cache before running.
//if(! is_admin()) {

add_filter('woocommerce_checkout_update_order_review', function() {

    WC()->session->set( 'cart_total', WC()->cart->cart_contents_total );

    $packages = WC()->cart->get_shipping_packages();

    foreach($packages as $key => $value) {
        $shipping_session = "shipping_for_package_$key";
        unset(WC()->session->$shipping_session);
    }
});

add_filter('woocommerce_package_rates', function($rates) {
    $algo       = new variShippingAlgo();
    $calculated = $algo->calculate_shipping_rate();

    WC()->session->set('calculated_rate', ceil($calculated));

    foreach($rates as $rate) {

        if($rate->get_method_id() == 'flat_rate' && strpos($rate->get_label(), 'UPS') !== false) {
            $rate->set_cost(ceil($calculated));
        }
    }

    $cart_total = floatval(WC()->cart->cart_contents_total) + floatval(WC()->cart->get_tax_totals()) + ceil($algo->get_total());
    WC()->session->set( 'algo_cart_total', $cart_total );
    WC()->session->set( 'cart_tax', floatval(WC()->cart->get_tax_totals()) );

    return $rates;
}, 99);
add_filter( 'woocommerce_shipping_calculator_enable_country', '__return_false', 99999999999999 );

//}

function dvpi_add_to_cart_validation( $passed, $product_id, $quantity, $variation_id = 0, $variations = null) {
    $algo = new variShippingAlgo();
    $passed = $algo->check_product_in_shop_algo_table( $passed, $product_id, $quantity, $variation_id, $variations);
    if ( !$passed ) {
        wc_add_notice( __("There is a problem with the product's shipping. Please contact the support at sales@variscite.com" , "shipping-algo"), "error" );
    }
    return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'dvpi_add_to_cart_validation', 10, 5 );


add_action( 'admin_notices', function() {
    $algo = new variShippingAlgo();
    $algo->check_if_product_in_algo_table();
    $algo->verify_table_skus();
} );

add_filter('wc_session_expiring', 'so_26545001_filter_session_expiring' );

function so_26545001_filter_session_expiring($seconds) {
    return 60 * 60 * 24 * 30; // 23 hours
}

add_filter('wc_session_expiration', 'so_26545001_filter_session_expired' );

function so_26545001_filter_session_expired($seconds) {
    return 60 * 60 * 24 * 30; // 24 hours
}