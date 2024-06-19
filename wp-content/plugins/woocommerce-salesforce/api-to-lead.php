<?php
    class wooToSFDC_api_to_lead extends wooToSFDC {

        function __construct() {

            # SFDC Auth Information
            $this->sfdc_username = 'hadas.s@variscite.com';
            $this->sfdc_password = 'Sh102030';
            $this->sfdc_se_token = 'FZH6Hm8zOGtOVYa2UADxLF73t';

            $this->wsdl = __DIR__ . '/soapclient/partner.wsdl.xml';

            # WP information
            $this->admin_email = array(get_option('admin_email'), 'lena.g@variscite.com', 'eden.d@variscite.com', 'roi@designercoded.com','allonsacks@gmail.com');
//            $this->admin_email = array('omer@problemsolver.co.il');
//            $this->admin_email = 'allonsacks@gmail.com,roi@designercoded.com';

            $this->postdata = $_POST;
        }

        // Insert new lead into SFDC
        function create_new_lead($order_id) {
            global $woocommerce;

            $SFDC = $this->SFDC_auth();

            $order_full = new WC_Order($order_id);
            $order_data = $order_full->get_data();
//			$order_data = array();
            $order_items = $order_full->get_items();
            $order_number = $order_full->get_order_number();

            // Privacy Policy field
            if(isset($_POST['privacy-policy'])) {
                $order_data['privacy_policy'] = date('c', time());
            }

            $sfdc_key_pairs = $this->ordered_sfdc_key_pairs();
            $sfdc_fields_filled = $this->fill_sfdc_lead_array($order_id, $order_number, $order_items, $sfdc_key_pairs, $order_data);

            try {
                $records = array();

                $records[0] = new SObject();
                $records[0]->type = 'Lead';
                $records[0]->fields = $sfdc_fields_filled;

                $response = $SFDC->create($records);

                if ($response[0]->success == true) {
                    $lead_id = $response[0]->id;
                    update_post_meta($order_id, "_lead_id", $lead_id);
                    update_field( 'order-salesforce_error', '', $order_id );
                } else {
                    update_field( 'order-salesforce_error', json_encode($response), $order_id );

                    $order = wc_get_order( $order_id );
                    $data  = $order->get_data(); // The Order data
                    $billing_email = $data['billing']['email'];
                    $errmessage = "Order id: ".$order_id."<br/><br/>\n";
                    $errmessage .= "Customer's email: ".$billing_email.": <br/><br/>\n";
                    $errmessage .= json_encode($response) . "\n" . json_encode($sfdc_fields_filled);

                    wp_mail($this->admin_email, "Variscite Store: Salesforce Integration Failure", $errmessage);
                }

            } catch (SoapFault $e) {

                $order = wc_get_order( $order_id );
                $data  = $order->get_data(); // The Order data
                $billing_email = $data['billing']['email'];


                # Catch and send out email to support if there is an error
                $errmessage =  "Exception ".$e->faultstring."<br/><br/>\n";
                $errmessage .= "Order id: ".$order_id."<br/><br/>\n";
                $errmessage .= "Customer's email: ".$billing_email.": <br/><br/>\n";
                $errmessage .= "Last Request:<br/><br/>\n";
                $errmessage .= $SFDC->getLastRequestHeaders();
                $errmessage .= "<br/><br/>\n";
                $errmessage .= $SFDC->getLastRequest();
                $errmessage .= "<br/><br/>\n";
                $errmessage .= "Last Response:<br/><br/>\n";
                $errmessage .= $SFDC->getLastResponseHeaders();
                $errmessage .= "<br/><br/>\n";
                $errmessage .= $SFDC->getLastResponse();

                update_field( 'order-salesforce_error', json_encode($e), $order_id );
                wp_mail($this->admin_email, "Variscite Store: Salesforce Integration Failure", json_encode($errmessage));

            } catch (Exception $e) {

                $order = wc_get_order( $order_id );
                $data  = $order->get_data(); // The Order data
                $billing_email = $data['billing']['email'];

                # Catch and send out email to support if there is an error
                $errmessage =  "Exception ".$e->faultstring."<br/><br/>\n";
                $errmessage .= "Order id: ".$order_id."<br/><br/>\n";
                $errmessage .= "Customer's email: ".$billing_email."<br/><br/>\n";
                $errmessage .= "Last Request:<br/><br/>\n";
                $errmessage .= $SFDC->getLastRequestHeaders();
                $errmessage .= "<br/><br/>\n";
                $errmessage .= $SFDC->getLastRequest();
                $errmessage .= "<br/><br/>\n";
                $errmessage .= "Last Response:<br/><br/>\n";
                $errmessage .= $SFDC->getLastResponseHeaders();
                $errmessage .= "<br/><br/>\n";
                $errmessage .= $SFDC->getLastResponse();

                update_field( 'order-salesforce_error', json_encode($e), $order_id );
                wp_mail($this->admin_email, "Variscite Store: Salesforce Integration Failure", json_encode($errmessage));
            }
        }

        function update_lead_data($formdata) {

            if (! empty( $formdata['invoice'] ) && ! empty($formdata['custom'])) {

                if( $formdata['payment_status'] == 'Completed' ) {

                    $order_data = json_decode(str_replace('\"', '"', $formdata['custom']));
                    $order_id = $order_data->order_id;

                    $SFDC = $this->SFDC_auth();

                    try {

                        $records = array();
                        $records[0] = new SObject();
                        $records[0]->type = 'Lead';

                        $records[0]->fields = array(
                            'id' => get_post_meta($order_id, '_lead_id')[0],
                            'Payment_approval__c'=> 'true'
                        );

                        $response = $SFDC->update($records);

                        if($response[0]->success != true){
                            update_field( 'order-salesforce_error', json_encode($response), $order_id );

                            $order = wc_get_order( $order_id );
                            $data  = $order->get_data(); // The Order data
                            $billing_email = $data['billing']['email'];
                            $errmessage = "Order id: ".$order_id."<br/><br/>\n";
                            $errmessage .= "Customer's email: ".$billing_email.": <br/><br/>\n";
                            $errmessage .= json_encode($response) . "\n";

                            wp_mail($this->admin_email, "Variscite Store: Salesforce Integration Failure - After Payment", $errmessage);
                        }

                    } catch (SoapFault $e) {

                        $order = wc_get_order( $order_id );
                        $data  = $order->get_data(); // The Order data
                        $billing_email = $data['billing']['email'];

                        # Catch and send out email to support if there is an error
                        $errmessage =  "Exception ".$e->faultstring."<br/><br/>\n";
                        $errmessage .= "Order id: ".$order_id."<br/><br/>\n";
                        $errmessage .= "Customer's email: ".$billing_email."<br/><br/>\n";
                        $errmessage .= "Last Request:<br/><br/>\n";
                        $errmessage .= $SFDC->getLastRequestHeaders();
                        $errmessage .= "<br/><br/>\n";
                        $errmessage .= $SFDC->getLastRequest();
                        $errmessage .= "<br/><br/>\n";
                        $errmessage .= "Last Response:<br/><br/>\n";
                        $errmessage .= $SFDC->getLastResponseHeaders();
                        $errmessage .= "<br/><br/>\n";
                        $errmessage .= $SFDC->getLastResponse();

                        update_field( 'order-salesforce_error', json_encode($e), $order_id );
                        wp_mail($this->admin_email, "Variscite Store: Salesforce Integration Failure", json_encode($errmessage));

                    } catch(Exception $e) {

                        $order = wc_get_order( $order_id );
                        $data  = $order->get_data(); // The Order data
                        $billing_email = $data['billing']['email'];

                        # Catch and send out email to support if there is an error
                        $errmessage =  "Exception ".$e->faultstring."<br/><br/>\n";
                        $errmessage .= "Order id: ".$order_id."<br/><br/>\n";
                        $errmessage .= "Customer's email: ".$billing_email."<br/><br/>\n";
                        $errmessage .= "Last Request:<br/><br/>\n";
                        $errmessage .= $SFDC->getLastRequestHeaders();
                        $errmessage .= "<br/><br/>\n";
                        $errmessage .= $SFDC->getLastRequest();
                        $errmessage .= "<br/><br/>\n";
                        $errmessage .= "Last Response:<br/><br/>\n";
                        $errmessage .= $SFDC->getLastResponseHeaders();
                        $errmessage .= "<br/><br/>\n";
                        $errmessage .= $SFDC->getLastResponse();

                        update_field( 'order-salesforce_error', json_encode($e), $order_id );
                        wp_mail($this->admin_email, "Variscite Store: Salesforce Integration Failure - After Payment", json_encode($errmessage));
                    }
                }
            }

        }

        function SFDC_auth() {

            // Init salesforce connection
            $mySforceConnection = new SforcePartnerClient();
            $mySforceConnection->createConnection($this->wsdl);
            $mySforceConnection->login($this->sfdc_username, $this->sfdc_password . $this->sfdc_se_token);

            return $mySforceConnection;
        }

        function fill_sfdc_lead_array($order_id, $order_number, $order_items, $sfdc_key_pairs, $order_data) {

            $sfdc_fields = array();
            $sfdc_fields['leadSource'] = __('Woocommerce', 'variscite-checkout');

            $order = new WC_Order($order_id);

            foreach($sfdc_key_pairs as $sfdc_field_key => $sfdc_key_pair) {

                switch($sfdc_field_key) {

                    case 'billing':

                        foreach($sfdc_key_pairs['billing'] as $woo_name => $sfdc_name) {

                            if($woo_name === 'country') {
                                $sfdc_fields[$sfdc_name] = htmlspecialchars(WC()->countries->countries[$order_data['billing'][$woo_name]]);
                            } else if($woo_name === 'state') {
                                $sfdc_fields[$sfdc_name] = htmlspecialchars(WC()->countries->get_states($order_data['billing']['country'])[$order_data['billing'][$woo_name]]);
                            } else if($woo_name === 'address_1') {
                                $sfdc_fields[$sfdc_name] = htmlspecialchars($order_data['billing']['address_1'] . ' ' . $order_data['billing']['address_2']);
                            } else if($woo_name == 'address_2') {
                                continue;
                            } else {

                                if(strpos($woo_name, 'dup_') !== false) {
                                    $woo_name = str_replace('dup_', '', $woo_name);
                                }

                                $sfdc_fields[$sfdc_name] = htmlspecialchars($order_data['billing'][$woo_name]);
                            }
                        }

                        break;

                    case 'shipping':

                        foreach($sfdc_key_pairs['shipping'] as $woo_name => $sfdc_name) {

                            if($woo_name === 'country') {
                                $sfdc_fields[$sfdc_name] = htmlspecialchars(WC()->countries->countries[$order_data['shipping'][$woo_name]]);
                            } else if($woo_name === 'state') {
                                $sfdc_fields[$sfdc_name] = htmlspecialchars(WC()->countries->get_states($order_data['shipping']['country'])[$order_data['shipping'][$woo_name]]);
                            } else if($woo_name === 'address_1') {
                                $sfdc_fields[$sfdc_name] = htmlspecialchars($order_data['shipping']['address_1'] . ', ' . $order_data['shipping']['address_2']);
                            } else if($woo_name == 'address_2') {
                                continue;
                            } else {
                                if(strpos($woo_name, 'dup_') !== false) {
                                    $woo_name = str_replace('dup_', '', $woo_name);
                                }

                                $sfdc_fields[$sfdc_name] = htmlspecialchars($order_data['shipping'][$woo_name]);
                            }
                        }

                        break;

                    case 'terms_conditions':
                        $sfdc_fields[$sfdc_key_pair] = date('c', time());
                        break;

                    case 'estimated_project_quantities':
                        $sfdc_fields[$sfdc_key_pair] = htmlspecialchars(get_post_meta($order_id, '_estimated_product_quantities', true));
                        break;

                    case 'order_details':
                        $sfdc_fields[$sfdc_key_pair] = htmlspecialchars($this->fill_order_info_field($order_items));
                        break;

                    case 'order_id';
                        $sfdc_fields[$sfdc_key_pair] = htmlspecialchars($order_number);
                        break;

                    case 'order_page_link':
                        $the_url = get_home_url() . '/checkout/order-received/' . $order_id . '/?key=' . $order_data['order_key'] . '&utm_nooverride=1&count=no';
                        $sfdc_fields[$sfdc_key_pair] = htmlspecialchars($the_url);
                        break;

                    case 'payment_approval':
                        $sfdc_fields[$sfdc_key_pair] = 'false';
                        break;

                    default:

                        if($order_data[$sfdc_field_key] && ! empty($order_data[$sfdc_field_key])) {
                            $sfdc_fields[$sfdc_key_pair] = htmlspecialchars($order_data[$sfdc_field_key]);
                        } else {

                            $value = $order->get_meta("_$sfdc_field_key");

                            $utm_fields = array(
                                'Campaign_medium__c', 'Campaign_source__c', 'Campaign_term__c', 'Page_url__c',
                                'Paid_Campaign_Name__c', 'curl', 'Campaign_content__c', 'GA_id__c'
                            );

                            if($value && ! empty($value) && $value != 'undefined') {
                                $sfdc_fields[$sfdc_key_pair] = htmlspecialchars($value);
                            } else if(in_array($sfdc_key_pair, $utm_fields)) {
                                $sfdc_fields[$sfdc_key_pair] = 'not_specified';
                            }

                        }

                        break;
                }
            }

            return $sfdc_fields;
        }

        function fill_order_info_field($order_items) {
            $order_info = '';

            foreach($order_items as $item_key => $item_value) {

                $item_data = $item_value->get_data();

                if($item_data['variation_id'] != 0) {
                    $product_vari = wc_get_product( $item_data['variation_id'] );
                    $sku = $product_vari->get_sku();
                    $order_info .= $sku . " | ";
                    $order_info .= "QTY: " . $item_data['quantity'] . "\n";
                }
                else {
                    $_product = wc_get_product( $item_data['product_id'] );
                    $sku = $_product->get_sku();
                    $order_info .= $sku . " | ";
                    $order_info .= "QTY: " . $item_data['quantity'] . "\n";
                }

                $order_info = substr($order_info, 0, 255);
            }

            return $order_info;
        }

        static function ordered_sfdc_key_pairs() {

            $sfdc_key_pairs = get_field('woo_to_sfdc_field_ids', 'option');
            $ordered_key_pairs = array();

            foreach($sfdc_key_pairs as $key_pair) {

                if(strpos($key_pair['woo_to_sfdc_woo_field_name'], 'billing_') !== false) {
                    $billing_key = str_replace('billing_', '', $key_pair['woo_to_sfdc_woo_field_name']);
                    $ordered_key_pairs['billing'][$billing_key] = $key_pair['woo_to_sfdc_sfdc_field_id'];
                }

                else if(strpos($key_pair['woo_to_sfdc_woo_field_name'], 'shipping_') !== false) {
                    $billing_key = str_replace('shipping_', '', $key_pair['woo_to_sfdc_woo_field_name']);
                    $ordered_key_pairs['shipping'][$billing_key] = $key_pair['woo_to_sfdc_sfdc_field_id'];
                }

                else {
                    $ordered_key_pairs[$key_pair['woo_to_sfdc_woo_field_name']] = $key_pair['woo_to_sfdc_sfdc_field_id'];
                }
            }

            return $ordered_key_pairs;
        }
    }