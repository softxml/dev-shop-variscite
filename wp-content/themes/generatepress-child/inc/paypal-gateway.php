<?php
add_filter( 'woocommerce_payment_gateways', 'variscite_paypal_add_gateway_class' );
function variscite_paypal_add_gateway_class( $gateways ) {
    $gateways[] = 'WC_Variscite_Paypal_Gateway'; // your class name is here
    return $gateways;
}
add_action( 'init', 'variscite_paypal_init_gateway_class' );
function variscite_paypal_init_gateway_class() {
    class WC_Variscite_Paypal_Gateway extends WC_Payment_Gateway {
        
        protected $client_id;
        
        public function __construct(){

            $this->id = 'variscite_paypal_paypal_payment';
            $this->icon = '';
            $this->has_fields = true;
            $this->method_title = __('Variscite Paypal Payment','variscite');
            $this->method_description = __('Create Payment Using Paypal','variscite');

            $this->supports = array(
                'products'
            );            

            $this->init_form_fields();

            // Load the settings.
            $this->init_settings();        
            $this->title = $this->get_option( 'title' );
            $this->enabled = $this->get_option('enabled');
            $this->testmode = 'yes' === $this->get_option( 'testmode' );

            $this->client_id = $this->testmode ? $this->get_option( 'sandbox_client_id', '' ) : $this->get_option( 'production_client_id', '' );

            // This Action hook saves the settings
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			
			add_action( 'woocommerce_api_wc_gateway_variscite_paypal', array( $this, 'check_ipn_response' ) );
			
            // We need custom JavaScript to obtain a token
            add_action( 'wp_enqueue_scripts', array( $this, 'variscite_paypal_load_scripts' ) );

            //Add Custom Paypal Button
            add_action( 'checkout_after_privacy_policy', array( $this, 'variscite_woocommerce_order_review' ), 10 );
            
            /*** Add Custom Payment Gayeway Css ***/
            add_action( 'wp_head', array( $this,'variscite_inline_css' ));
        }
		
		public function check_ipn_response(){
			if( isset( $_REQUEST['order_id'] ) && isset( $_REQUEST['bypass_vari'] ) && $_REQUEST['bypass_vari'] == 'yes' ){
				echo 'in it';
				$order = new WC_Order( $_REQUEST['order_id'] );
				if( $order ){
					$order->update_status( 'wc-processing' );
					exit;
				}
			}
			
			$raw_post_data = file_get_contents( 'php://input' );
			
			$f = fopen( ABSPATH."paypal-ipn-debug.log", "a+" );
			fwrite( $f, '-----'.date( 'Y-m-d H:i:s' ).'------' );
			fwrite( $f, PHP_EOL );
			fwrite( $f, print_r( $raw_post_data, true ) );
			fwrite( $f, PHP_EOL );
			fclose( $f );
			
			$data = json_decode( $raw_post_data );
			if( isset( $data->resource_type ) && strtolower( $data->resource_type ) == "capture" ){
				if( isset( $data->resource->custom_id ) ){
					$order_id = (int)$data->resource->custom_id;
					if( !$order_id ){
						global $wpdb;
						
						$paypal_order_id = $data->resource->supplementary_data->related_ids->order_id;
						$orderMeta = $wpdb->get_row( "select post_id, meta_key from $wpdb->postmeta where meta_value = '".$paypal_order_id."'" );
						if( $orderMeta ){
							$order_id = $orderMeta->post_id;
						}
					}
					if( $order_id ){
						$order = new WC_Order( $order_id );
						if( $order ){
							if( isset( $data->event_type ) && $data->event_type == "PAYMENT.CAPTURE.COMPLETED" ){
								$order->update_status( 'wc-processing' );
								
								$triggerCalled = get_post_meta( $order_id, 'is_paypal_trigger_called', true );
								if( $triggerCalled != 'yes' ){
									//call paypal ipn
									$formdata = array();
									$formdata['payment_status'] = 'Completed';
									$formdata['order_id'] = $order_id;
									do_action( 'valid-variscite-paypal-ipn-request', $formdata );
									
									do_action( 'vari_trigger_new_order_email', $order_id );
									//do_action( 'valid-paypal-standard-ipn-request', $formdata );
									
									update_post_meta( $order_id, 'is_paypal_trigger_called', 'yes' );
								}
							}else{
								$order->update_status( 'wc-cancelled', 'order_note' );
							}
						}
					}
				}
			}
			exit;
		}
		
        /*** Add Payment Gateway Fields ***/
        public function init_form_fields(){
            $this->form_fields = array(
                'enabled' => array(
                    'title'         => __( 'Enable/Disable', 'woocommerce-other-payment-gateway' ),
                    'type'          => 'checkbox',
                    'label'         => __( 'Enable Custom Paypal', 'woocommerce' ),
                    'default'       => 'no'
                ),

                'title' => array(
                    'title'         => __( 'Title', 'woocommerce' ),
                    'type'          => 'text',
                    'description'   => __( 'This controls the title', 'woocommerce' ),
                    'default'       => __( 'Variscite Paypal Payment', 'woocommerce' ),
                    'desc_tip'      => true,
                ),
                'description' => array(
                    'title' => __( 'Description', 'woocommerce' ),
                    'type' => 'textarea',
                    'css' => 'width:500px;',
                    'default' => 'Variscite Paypal Payment Description.',
                    'description'   => __( 'The message which you want it to appear to the customer in the checkout page.', 'woocommerce-other-payment-gateway' ),
                ),
                'testmode' => array(
                    'title'       => 'Test mode',
                    'label'       => 'Enable Test Mode',
                    'type'        => 'checkbox',
                    'description' => 'Place the payment gateway in test mode using test API keys.',
                    'default'     => 'yes',
                    'desc_tip'    => true,
                ),
                'sandbox_client_id' => array(
                    'title'       => 'Sandbox Client ID',
                    'type'        => 'text',
                ),
                'production_client_id' => array(
                    'title'       => 'Production Client ID',
                    'type'        => 'text',
                )
            );
        }

        /*** Include Paypal Javascript ***/
        public function variscite_paypal_load_scripts(){            
            // we need JavaScript to process a token only on cart/checkout pages, right?
            if ( ! is_checkout() && ! isset( $_GET['pay_for_order'] ) ) {
                return;
            }

            // if our payment gateway is disabled, we do not have to enqueue JS too
            if ( 'no' === $this->enabled ) {
                return;
            }
            // error_log("client_id ". $this->client_id);
            // if ( empty( $this->client_id ) ) {
            //  return;
            // }

            // do not work with card detailes without SSL unless your website is in a test mode         
            if ( ! $this->testmode && ! is_ssl() ) {
                return;
            }
            
            $client_id = $this->client_id;

            $paypal_api_js = add_query_arg( array(
                'client-id' => $client_id,
                'components' => 'buttons',
                'currency' => 'USD',
                'debug' => 'false',
                'disable-funding' => 'credit,paylater,paylater,venmo,bancontact,blik,eps,giropay,mercadopago,mybank,p24,sepa,sofort,ideal',
                'enable-funding' => 'card'
            ), 'https://www.paypal.com/sdk/js' );

            wp_enqueue_script( 
                'paypal-sadbox', #handle 
                $paypal_api_js, #src 
                array( 'jquery' ), #deps 
                null, #ver 
                true #in_footer
            );

            wp_enqueue_script( 
                'paypal-sadbox-handler', #handle 
                get_stylesheet_directory_uri()."/js/paypal-sadbox-handler.js", #src 
                array( 'jquery' ), #deps 
                time(), #ver 
                true #in_footer
            );

            $user_id = get_current_user_id();
            wp_localize_script( 'paypal-sadbox-handler', 'paypal_ajax', 
                array( 
                    'url' => admin_url( 'admin-ajax.php' ),
                    'user_id' =>  $user_id,
					'shipping_error' =>  __( 'An unexpected error has occurred, Please contact us via the website or email at <a href="mailto:sales@variscite.com">sales@variscite.com</a>' )
                ) 
            );          
        }     

        /*** Display Paypal Button in Checkout Page ***/
        public function variscite_woocommerce_order_review(){
            if( is_checkout() ){
                echo '<div id="variscite-paypal-container"></div>';
                echo '<div style="display:none"><button id="variscite-paypal-test-order">Test Order</button></div>';
                echo '<div class="variscite-loader" style="display:none"><img src='.get_stylesheet_directory_uri()."/images/loader_spinner.gif".'></div>';
            }
        }
                
        /*** Handler function for hide woocommerce payment html section ***/
        public function variscite_inline_css () { 
            if( is_checkout() ){ ?>
                <style>
                .form-row.place-order{
                    display: none !important;
                }
                #place_order{
                    display: none !important;
                }
                #variscite-paypal-container{
                    padding-top: 15px;
                    padding-bottom: 15px;
                }
                </style>
                <?php 
            }
        }       
    }
}
/*Custom Payment Gateway Code End*/

/*** Ajax call setup to create order when user click on payment button and paypal window is opened ***/
add_action( 'wp_ajax_variscite_paypal_ajax_form_validation', 'variscite_paypal_ajax_form_validation_handle' );
add_action( 'wp_ajax_nopriv_variscite_paypal_ajax_form_validation', 'variscite_paypal_ajax_form_validation_handle' );

/*** Handler function for order creation with payment status pending ***/
function variscite_paypal_ajax_form_validation_handle(){
    $response = array();
        
    if( isset($_POST['checkout_data']) && ! empty($_POST['checkout_data']) ) {
        $checkout_data = $_REQUEST['checkout_data'];    
        $form_data = array();
        parse_str($checkout_data, $form_data);

        //error_log("form_data ==> ".print_r($form_data,true));
        $billing_first_name = $form_data['billing_first_name'];
        $billing_last_name = $form_data['billing_last_name'];
        $billing_address_1 = $form_data['billing_address_1'];
        $billing_city = $form_data['billing_city'];
        //$billing_state = $form_data['billing_state'];
        $billing_country = $form_data['billing_country'];
        $billing_postcode = $form_data['billing_postcode'];
        $billing_phone = $form_data['billing_phone'];
        $billing_email = $form_data['billing_email'];
        $billing_conf_email = $form_data['billing_email_addr_confirmation'];
                
        if ( empty( $billing_first_name ) ){
            wc_add_notice( __( '<strong>Billing First name</strong> is a required field.' ), 'error' );
        }

        if( empty( $billing_last_name ) ){
            wc_add_notice( __( '<strong>Billing Last name</strong> is a required field.' ), 'error' );
        }

        if( empty( $billing_address_1 ) ){
            wc_add_notice( __( '<strong>Billing Street address</strong> is a required field.' ), 'error' );
        }

        if( empty( $billing_city ) ){
            wc_add_notice( __( '<strong>Billing Town / City</strong> is a required field.' ), 'error' );
        }
            
        $is_valid_zip = WC_Validation::is_postcode( esc_html($billing_postcode), esc_html($billing_country) );      
        if( empty( $billing_postcode ) ){
            wc_add_notice( __( '<strong>Billing ZIP Code</strong> is a required field.' ), 'error' );
        }elseif ( !empty($billing_postcode) && !$is_valid_zip ){
            wc_add_notice( '<strong>Billing ZIP Code</strong> is not a valid ZIP code.' , 'error' );
        } 
        
        $is_valid_phone = WC_Validation::is_phone(esc_html($billing_phone));
        if( empty( $billing_phone ) ){
            wc_add_notice( __( '<strong>Billing Phone</strong> is a required field.' ), 'error' );
        }elseif ( !empty($billing_phone) && !$is_valid_phone ){
            wc_add_notice( '<strong>Billing Phone</strong> is not a valid phone number.' , 'error' );
        }elseif ( strlen( $billing_phone ) > 20 ){
            wc_add_notice( '<strong>Billing Phone</strong> is must be under 20 characters.' , 'error' );
        }  

        if( empty( $billing_email ) ){
            wc_add_notice( __( '<strong>Billing Email address</strong> is a required field.' ), 'error' );
        }elseif( strpos($billing_email, '@') == false ){
            wc_add_notice( __( '<strong>Billing Email address</strong> is not valid email address.' ), 'error' );
        }elseif ( !empty( $billing_email ) && $billing_email != "onintay@gmail.com" ){
            $exp = explode('@', $billing_email);
            if ( $exp[1] == 'gmail.com' || $exp[1] == 'com' || $exp[1] == '.com' ) {  
                wc_add_notice( __( 'Please enter your company email address.' ), 'error' );
            }
        }

        // elseif( !filter_var( $billing_email, FILTER_VALIDATE_EMAIL ) ){
        //     wc_add_notice( __( '<strong>Billing Email address</strong> is Invalid' ), 'error' );
        // }     

        if( empty( $billing_conf_email ) ){
            wc_add_notice( __( '<strong>Billing Confirm Email address</strong> is a required field.' ), 'error' );
        }elseif(  $billing_email != $billing_conf_email ){  
            wc_add_notice( __( 'Email and Confirm Email not matched.' ), 'error' );
        }

        if( empty( $form_data['terms'] ) ){
            wc_add_notice( __( 'Please accept terms and conditions.' ), 'error' );
        }

        // if( empty( $form_data['privacy-policy'] ) ){
        //     wc_add_notice( __( 'Please accept privacy policy.' ), 'error' );
        // }
    }

    wc_print_notices();
    die();
}

function get_shipping_name_by_id( $shipping_id ) {
    $packages = WC()->shipping->get_packages();

    foreach ( $packages as $i => $package ) {
        if ( isset( $package['rates'] ) && isset( $package['rates'][ $shipping_id ] ) ) {
            $rate = $package['rates'][ $shipping_id ];
            /* @var $rate WC_Shipping_Rate */
            return $rate->get_label();
        }
    }

    return '';
}

add_action( 'wp_ajax_variscite_save_cart_details', 'variscite_save_cart_details_cb' );
add_action( 'wp_ajax_nopriv_variscite_save_cart_details', 'variscite_save_cart_details_cb' );
function variscite_save_cart_details_cb(){
	/* $cart = WC()->cart;
	$checkout = WC()->checkout;
	WC()->session->set( 'custom_cart_obj', $cart );
	
	$algo = new variShippingAlgo();
	$algo->get_shipping_zone_config();
	$calculated = $algo->calculate_shipping_rate();
	$a = WC()->session->get('customer'); */
	$a = get_shipping_name_by_id( WC()->session->get( 'chosen_shipping_methods' )[0] );
	echo print_r( $a );
	exit;
}

/* add_action( 'woocommerce_checkout_create_order_shipping_item', function( $item, $package_key, $package, $order ){
	$algoN = new variShippingAlgo();
	$algoN->get_shipping_zone_config();
	$calculated = $algoN->calculate_shipping_rate();
}, 10, 4 ); */
/*** Ajax call setup to create order when user click on payment button and paypal window is opened
 ***/
add_action('wp_ajax_variscite_paypal_ajax_order', 'variscite_paypal_ajax_order_handle');
add_action('wp_ajax_nopriv_variscite_paypal_ajax_order', 'variscite_paypal_ajax_order_handle');

/*** Handler function for order creation with payment status pending ***/
function variscite_paypal_ajax_order_handle(){
    $response = array();
    $form_values = array();
    $order_received_url = '';
        
    if( isset( $_POST['checkout_data'] ) && ! empty( $_POST['checkout_data'] ) ){		
        $order    = new WC_Order();
        $cart     = WC()->cart;
        $checkout = WC()->checkout;
        $data     = [];
        // Loop through posted data array transmitted via jQuery
        foreach( $_POST['checkout_data'] as $values ){
            // Set each key / value pairs in an array
            $data[$values['name']] = $values['value'];
        }
		
		$f = fopen( ABSPATH."create-order-dbg.log", "a+" );
		fwrite( $f, "-----".date( 'Y-m-d H:i:s' )."-----" );
		fwrite( $f, PHP_EOL );
		fwrite( $f, print_r( $_POST['checkout_data'], true ) );
		fwrite( $f, PHP_EOL );
		fwrite( $f, print_r( $cart, true ) );
		fwrite( $f, PHP_EOL );
		fwrite( $f, PHP_EOL );
		
		//check for empty cart
		if( $cart->is_empty() ){
			global $woocommerce;
			$cart = $woocommerce->cart;
			
			fwrite( $f, '----- New Cart -----' );
			fwrite( $f, PHP_EOL );
			fwrite( $f, print_r( $cart, true ) );
			fwrite( $f, PHP_EOL );
			fwrite( $f, PHP_EOL );
		}
		
		fwrite( $f, '------------------------------' );
		fwrite( $f, PHP_EOL );
		fwrite( $f, PHP_EOL );
		fclose( $f );
		
        $cart_hash = md5( json_encode( wc_clean( $cart->get_cart_for_session() ) ) . $cart->total );
        //$available_gateways = WC()->payment_gateways->get_available_payment_gateways();

        // Loop through the data array
        foreach ( $data as $key => $value ) {
            // Use WC_Order setter methods if they exist
            if ( is_callable( array( $order, "set_{$key}" ) ) ) {
                $order->{"set_{$key}"}( $value );

            // Store custom checkout_data prefixed with wither shipping_ or billing_
            } elseif ( ( 0 === stripos( $key, 'billing_' ) || 0 === stripos( $key, 'shipping_' ) )
                && ! in_array( $key, array( 'shipping_method', 'shipping_total', 'shipping_tax' ) ) ) {
                $order->update_meta_data( '_' . $key, $value );
            }
        }
			
		if( !isset( $data['ship_to_different_address'] ) ){
			foreach( $data as $key => $value ){
				if( stripos( $key, 'shipping_' ) === 0 ){
					$newKey = str_replace( 'shipping_', 'billing_', $key );
					if( isset( $data[$newKey] ) ){
						$order->update_meta_data( '_' . $key, $data[$newKey] );
					}
				}
			}
		}
		
		if( isset( $data['ship_to_different_address'] ) ){
			$order->update_meta_data( 'order_needs_shipping', 'yes' );
		}
		
        $order->set_created_via( 'checkout' );
        $order->set_cart_hash( $cart_hash );
        $order->set_customer_id( apply_filters( 'woocommerce_checkout_customer_id', isset($_POST['user_id']) ? $_POST['user_id'] : '' ) );
        $order->set_currency( get_woocommerce_currency() );
        $order->set_prices_include_tax( 'yes' === get_option( 'woocommerce_prices_include_tax' ) );
        $order->set_customer_ip_address( WC_Geolocation::get_ip_address() );
        $order->set_customer_user_agent( wc_get_user_agent() );
        $order->set_customer_note( isset( $data['order_comments'] ) ? $data['order_comments'] : '' );
        //$order->set_payment_method( isset( $available_gateways[ $data['payment_method'] ] ) ? $available_gateways[ $data['payment_method'] ]  : $data['payment_method'] );shipping
        $order->set_shipping_total( $cart->get_shipping_total() );
        $order->set_discount_total( $cart->get_discount_total() );
        $order->set_discount_tax( $cart->get_discount_tax() );
        $order->set_cart_tax( $cart->get_cart_contents_tax() + $cart->get_fee_tax() );
        $order->set_shipping_tax( $cart->get_shipping_tax() );
        $order->set_total( $cart->get_total( 'edit' ) );
		//$order->calculate_totals();
		
        $checkout->create_order_line_items( $order, $cart );
        $checkout->create_order_fee_lines( $order, $cart );
        $checkout->create_order_shipping_lines( $order, WC()->session->get( 'chosen_shipping_methods' ), WC()->shipping->get_packages() );
        $checkout->create_order_tax_lines( $order, $cart );
        $checkout->create_order_coupon_lines( $order, $cart );
		
		/* $algoN = new variShippingAlgo();
		$algoN->get_shipping_zone_config();
		$calculated = $algoN->calculate_shipping_rate();
		WC()->session->set( 'calculated_rate', ceil( $calculated ) );
		$order->set_shipping_total( $calculated );
				
		// create shipping object
		$shipping_id = WC()->session->get( 'chosen_shipping_methods' )[0];
		$shipping = new WC_Order_Item_Shipping();
		$shipping->set_method_title( get_shipping_name_by_id( $shipping_id ) );
		$shipping->set_method_id( $shipping_id );
		if( WC()->session->get('customer')['shipping_country'] != 'IL' ){
			$shipping->set_total( $calculated ); // optional
		}
		$order->add_item( $shipping ); */
		
		$order->calculate_totals();
		
		/*$f = fopen( ABSPATH."checkout-order-dbg.log", "a+" );
		fwrite( $f, "ship - ".$calculated );
		fwrite( $f, PHP_EOL );
		fwrite( $f, print_r( $cart, true ) );
		fwrite( $f, PHP_EOL );
		fwrite( $f, '------------------------------' );
		fwrite( $f, PHP_EOL );
		fwrite( $f, PHP_EOL );
		fclose( $f ); */
		
        /**
         * Action hook to adjust order before save.
         * @since 3.0.0
         */
        do_action( 'woocommerce_checkout_create_order', $order, $data );

        // Save the order.
        $order_id = $order->save();
		
		if( isset( $data['estimated_product_quantities'] ) ) {
			update_post_meta( $order_id, '_estimated_product_quantities', $data['estimated_product_quantities'] );
		}
		
        do_action( 'woocommerce_checkout_update_order_meta', $order_id, $data );
		
        // (@AsafA) start
        // do_action( 'woocommerce_checkout_order_processed', $order_id, $data, $order );
		// (@AsafA) end
		
        //echo 'New order created with order ID: #'.$order_id.'.' ;
        // self::process_payment(); 
        //error_log("ORDER DETAILS \n".print_r($order,true));
        $order_total = $order->get_total();

        $order_received_url =  $order->get_checkout_order_received_url();
		
        // (@AsafA) start
        try {
            do_action( 'woocommerce_checkout_order_processed', $order_id, $data, $order );
        } catch (\Exception $e) {
            $response = [
                'status' => 2,
                'data' => array(
                    'order_id' => $order_id,
                    'order_total' => $order_total,
                    'order_received_url' => $order_received_url,
                ),  
                'message' =>  $e->getMessage(),
            ];

            wp_send_json($response); 
            wp_die();
        }
        // (@AsafA) end

		// Check if exceeded the amount of tries for finding a non-zero field
		// in the shipping algo
		$exceeded_tries = WC()->session->get( 'exceeded_tries' );
		$exceeded_field = WC()->session->get( 'exceeded_field' );
		$freight_cost = WC()->session->get( 'freight_cost' );
		$fuel_cost = WC()->session->get( 'fuel_cost' );
		$offir = WC()->session->get( 'offir' );
		/* if( current_user_can( 'administrator' ) ){
			$fuel_cost = 0;
		} */
		
		$shipping_id = WC()->session->get( 'chosen_shipping_methods' )[0];
		if( strpos( $shipping_id, 'local_pickup' ) !== 0 && ( $exceeded_tries || $offir == 0 || $freight_cost == 0 || $fuel_cost == 0 ) ){
			$order->update_status( 'wc-lead' );
            $order->add_order_note( 'This order is a lead' );
			
			//add log if error throw
			$fa = fopen( ABSPATH."shipping-algo-dbg.log", "a+" );
			fwrite( $fa, "-----".date( "Y-m-d H:i:s" )."-----" );
			fwrite( $fa, PHP_EOL );
			fwrite( $fa, print_r( $shipping_id, true ) );
			fwrite( $fa, PHP_EOL );
			fwrite( $fa, 'exceeded_tries - '.$exceeded_tries );
			fwrite( $fa, PHP_EOL );
			fwrite( $fa, 'offir - '.$offir );
			fwrite( $fa, PHP_EOL );
			fwrite( $fa, 'freight_cost - '.$freight_cost );
			fwrite( $fa, PHP_EOL );
			fwrite( $fa, 'fuel_cost - '.$fuel_cost );
			fwrite( $fa, PHP_EOL );
			fwrite( $fa, PHP_EOL );
			fclose( $fa );
			
			$response = [
				'status' => 0,
				'message' => 'An unexpected error has occurred, Please contact us via the website or email at <a href="mailto:sales@variscite.com">sales@variscite.com</a>',
			];
			
			//wc_add_notice( 'An unexpected error has occurred, Please contact us via the website or email at <a href="mailto:sales@variscite.com">sales@variscite.com</a>', 'error' );
			
			// Also, dispatch an email about the error w/ the order's details
			$to      = array( 'sales@variscite.com', 'lena.g@variscite.com', 'ayelet.o@variscite.com', 'eden.d@variscite.com' );
			
			if( $freight_cost == 0 ){
				$exceeded_field = get_field_object( 'vari__algo_zones-costs--freight-multi', 'option')['label'];
			}else if( $fuel_cost == 0 ){
				$exceeded_field = get_field_object( 'vari__algo_zones-costs--fuel', 'option')['label'];
			}else if( $offir == 0 ){
				$exceeded_field = 'Total Shipping';
			}
			
			$subject = "A '0' value was found in the shipping calculation for order number $order_id";
			$message = "The following values are recognized as '0' on the order number $order_id";
			$message = "The field with value of '0' is $exceeded_field";
			checkout_error_email( $data, $to, $subject, $message );
		}else{
			$response = [
				'status' => 1,
				'data' => array(
					'order_id' => $order_id,
					'order_total' => $order_total,
					'order_received_url' => $order_received_url,
				),          
				'message' => 'order has been created',
			];      
        }      
    }else{
        $response = [
            'status' => 0,
            'message' => 'request data missing to create order',
        ];
    }
    
    wp_send_json($response); 
    wp_die();
}

/*** Ajax call setup to create order when user click on payment button and paypal window is opened ***/
add_action( 'wp_ajax_variscite_update_paypal_ajax_order', 'variscite_update_paypal_ajax_order_handle' );
add_action( 'wp_ajax_nopriv_variscite_update_paypal_ajax_order', 'variscite_update_paypal_ajax_order_handle');
/*** Handler function for order creation with payment status pending ***/
function variscite_update_paypal_ajax_order_handle(){
	$f = fopen( ABSPATH."paypal-debug.log", "a+" );
	fwrite( $f, '-----'.date( 'Y-m-d H:i:s' ).'------' );
	fwrite( $f, PHP_EOL );
	fwrite( $f, 'order id : '.$_POST['order_id'] );
	fwrite( $f, PHP_EOL );
	fwrite( $f, print_r( $_POST['details'], true ) );
	fwrite( $f, PHP_EOL );
	fwrite( $f, PHP_EOL );
	fclose( $f );
	
    $date = date('d/m/Y h:i:s a', time());    
    error_log( "Call Order Status Start ". $date );
    global $woocommerce;
    $response = array();
    $order_received_url = '';
	
	//check if order id is Empty
	if( isset( $_POST['details']['purchase_units'][0]['custom_id'] ) ){
		if( isset( $_POST['order_id'] ) && empty( $_POST['order_id'] ) ){
			$_POST['order_id'] = $_POST['details']['purchase_units'][0]['custom_id'];
		}else if( !isset( $_POST['order_id'] ) ){
			$_POST['order_id'] = $_POST['details']['purchase_units'][0]['custom_id'];
		}
	}
	
    if ( 
        isset($_POST['order_id']) && !empty($_POST['order_id']) && 
        isset($_POST['order_status']) && !empty($_POST['order_status']) 
    ) {
        $order_id = $_POST['order_id'];
        $order_status = $_POST['order_status'];
        error_log( "Order ID: ". $order_id );
        error_log( "order_status: ". $order_status );
        $order = new WC_Order($order_id);
		
		if( isset( $_POST['details'] ) ){
			update_post_meta( $order_id, 'paypal_capture_res', json_encode( $_POST['details'] ) );
		}
		
		if( isset( $_POST['details']['id'] ) ){
			update_post_meta( $order_id, 'paypal_order_id', $_POST['details']['id'] );
		}
		
        if( $order_status == 'cancel' ){
            $order->update_status('wc-cancelled', 'order_note');
            error_log( "Cancel" );

            $order_received_url =  '';
        }

        if( $order_status == 'process' ){
			//$order->update_status( 'wc-processing' );
            //wp_schedule_single_event(time(), 'update_new_order_event_1', array($order_id));
            // error_log("Start Order Update Status ". $date );
            // $order->update_status( 'wc-processing' );
            // error_log( "Process" );
            
            // Empty Cart
            if( is_cart() || is_checkout() ) {
                WC()->cart->empty_cart();
            }
            
            $order_received_url =  $order->get_checkout_order_received_url();            
            error_log("End Order Update Status ". $date );

        }
        $response = [
            'status' => 1,
            'order_received_url' => $order_received_url,
            'message' => 'Successfully',
        ];

    }else{
        $response = [
            'status' => 0,
            'message' => 'something wrong',
        ];
    }

    wp_send_json( $response ); 
    wp_die();
}

/** Update Order using API Code Start **/
function do_update_new_order_status($order_id) {

    global $woocommerce;
    $order = new WC_Order( $order_id );
    $order->update_status('wc-processing', 'order_note');
    error_log( "Before Cart Empty" );

    // Make sure it's only on front end
    if ( is_admin() ) return false;

    // Empty Cart
    // if( is_cart() || is_checkout() ) {
    //     WC()->cart->empty_cart();
    // }
    error_log( "After Cart Empty" );
}
add_action( 'update_new_order_event_1','do_update_new_order_status' );

//add_action( 'woocommerce_thankyou', 'woocommerce_auto_processing_orders');
function woocommerce_auto_processing_orders( $order_id ) {
    if ( ! $order_id )
        return;

    $order = new WC_Order( $order_id );

    // If order is "on-hold" update status to "processing"
    //$order->update_status( 'wc-processing' );
    $order = wc_get_order( $order_id );
    $order->update_status( 'processing' );

    // Empty Cart
    // if( is_cart() || is_checkout() ) {
    //     WC()->cart->empty_cart();
    // }

}
   
/** Update Woo Commerce Order using API Code Start **/
//add_action('init','wc_update_order_api');
function wc_update_order_api(){
    $order_id = 17255;
    global $woocommerce;

    $url = 'https://paypal-dev.variscite.co.uk/wp-json/wc/v3/orders/'.$order_id;    

    $api_url = add_query_arg( array(
        'consumer_key' => 'ck_66e76db23c743843b307d9444b76ad088bea3d09',
        'consumer_secret' => 'cs_0e9871301383aaf450bf75c98dda8dcb2d32f2dd',
    ), $url );
    
    $body = array(
        'status' => 'completed'
    );

    $headers = array(
        'Authorization' => 'Basic '.base64_encode('staging:Staging11'),
        'Content-Type' => 'application/json'
    );
    
    $data = array(
        'method'       => 'PUT',
        'body'         => $body,
        'headers'      => $headers
    );

    $response = wp_remote_request( $api_url, $data );
    
    error_log("Response \n". print_r($response,true));
    if ( ! is_wp_error( $response ) ) {
        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        error_log("Body Data \n". print_r($body,true));
        return $body;
    } else {
        $error_message = $response->get_error_message();
        error_log("error_message ". $error_message);
    }    
}
/** Update Woo Commerce Order using API Code Start **/

/* Test Order Call */
add_action( 'wp_ajax_variscite_paypal_ajax_test_order', 'variscite_paypal_ajax_test_order_handle' );
add_action( 'wp_ajax_nopriv_variscite_paypal_ajax_test_order', 'variscite_paypal_ajax_test_order_handle' );
function variscite_paypal_ajax_test_order_handle(){
    error_log("call fun");
    if ( isset($_POST['order_id']) && !empty( $_POST['order_id'] ) ) 
    {
        date_default_timezone_set('Asia/Kolkata');
        $order_id = $_POST['order_id'];        
        error_log("Start Time ". date("Y/m/d H:i:s", time()) );
        error_log("order_id == ". $order_id);
        if ( ! $order_id )
            return;

        $order = wc_get_order( $order_id );
        $order->update_status( 'processing' );
        error_log("End Time ". date("Y/m/d H:i:s",time()) );

        echo "Order Updated";
        die();
    }
}

add_filter( 'woocommerce_order_needs_shipping_address', function( $needs_address, $hide, $instance ){
	if( get_post_meta( $instance->get_id(), 'order_needs_shipping', true ) == 'yes' ){
		return true;
	}
	return $needs_address;
}, 10, 3 );

add_action( 'wp_ajax_vari_checkout_validate_shipping_cost', 'vari_checkout_validate_shipping_cost' );
add_action( 'wp_ajax_nopriv_vari_checkout_validate_shipping_cost', 'vari_checkout_validate_shipping_cost' );
function vari_checkout_validate_shipping_cost(){
	if( isset( $_POST['checkout_data'] ) && ! empty( $_POST['checkout_data'] ) ){
        $fields     = [];
        // Loop through posted data array transmitted via jQuery
        foreach( $_POST['checkout_data'] as $values ){
            // Set each key / value pairs in an array
            $fields[$values['name']] = $values['value'];
        }
		do_action( 'woocommerce_after_checkout_validation', $fields, array() );
	}
	exit;
}