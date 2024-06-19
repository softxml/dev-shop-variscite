<?php

    class variShippingAlgoUPS {

        private $shipper;
        private $ups_id;
        private $ups_pass;
        private $ups_access_key;

        function __construct() {

            // Get main language
            add_filter( 'acf/settings/current_language', function() {
                global $sitepress;
                return $sitepress->get_default_language();
            });

            $this->shipper = get_field('woo_ups__shipper_id', 'option');
            $this->ups_id = get_field('woo_ups__user_id', 'option');
            $this->ups_pass = get_field('woo_ups__user_password', 'option');
            $this->ups_access_key = get_field('woo_ups__access_key', 'option');

            // Reset to original language
            add_filter( 'acf/settings/current_language', function() {
                return ICL_LANGUAGE_CODE;
            });
        }

        public function check_for_remote_area() {
            $shipping_data = $this->get_customer_shipping_data();
            $from_address = $shipping_data['from'];
            $to_address = $shipping_data['to'];

            $ups_xml = $this->construct_ups_xml($from_address, $to_address, $shipping_data);
            $ups_resp_xml = $this->execute_ups_request($ups_xml);
			
            return $this->parse_xml_resp($ups_resp_xml);
        }

        private function construct_ups_xml($from, $to, $ups_data) {
            $data = $this->open_xml_request();
            $data .= $this->shipment_xml_info($from, $to);
            $data .= $this->shipment_xml_packages();
            $data .= $this->close_xml_request();

            return $data;
        }

        private function open_xml_request() {

            return '<?xml version="1.0"?>
                    <AccessRequest xml:lang="en-US">
                        <AccessLicenseNumber>' . $this->ups_access_key . '</AccessLicenseNumber>
                        <UserId>' . $this->ups_id . '</UserId>
                        <Password>' . $this->ups_pass . '</Password>
                    </AccessRequest>' . "\n" . "\n";
        }

        private function shipment_xml_info($from, $to) {

	        return '<?xml version="1.0"?>
                    <RatingServiceSelectionRequest xml:lang="en-US">
                        <Request>
                            <TransactionReference>
                                <CustomerContext>Bare Bones Rate Request</CustomerContext>
                                <XpciVersion>1.0001</XpciVersion>
                            </TransactionReference>
                            
                            <RequestAction>Rate</RequestAction>
                            <RequestOption>Rate</RequestOption>
                        </Request>
                        
                        <PickupType>
                            <Code>03</Code>
                        </PickupType>
                        
                        <Shipment>
                            <Shipper>
                                <Address>
                                    <PostalCode>' . $from['zip'] . '</PostalCode>
                                    <CountryCode>' . $from['addr']['country'] . '</CountryCode>
                                </Address>
                                
                                <ShipperNumber>' . $this->shipper . '</ShipperNumber>
                            </Shipper>
                            
                            <ShipTo>
                                <Address>
                                    <PostalCode>' . $to['zip'] . '</PostalCode>
                                    <CountryCode>' . $to['addr']['country'] . '</CountryCode>
                                    <ResidentialAddressIndicator/>
                                </Address>
                            </ShipTo>
                            
                            <ShipFrom>
                                <Address>
                                    <PostalCode>' . $from['zip'] . '</PostalCode>
                                    <CountryCode>' . $from['addr']['country'] . '</CountryCode>
                                </Address>
                            </ShipFrom>
                            
                            <Service>
                                <Code>65</Code>
                            </Service>' . "\n" . "\n";
        }

        private function shipment_xml_packages() {

            return '<Package>
                    <PackagingType>
                        <Code>02</Code>
                    </PackagingType>

                    <Dimensions>
                        <UnitOfMeasurement>
                            <Code>CM</Code>
                        </UnitOfMeasurement>

                        <Length>1</Length>
                        <Width>1</Width>
                        <Height>1</Height>
                    </Dimensions>

                    <PackageWeight>
                        <UnitOfMeasurement>
                            <Code>KGS</Code>
                        </UnitOfMeasurement>

                        <Weight>1</Weight>
                    </PackageWeight>
                </Package>' . "\n";
        }

        private function close_xml_request() {

            return '</Shipment>
                    </RatingServiceSelectionRequest>';
        }

        private function execute_ups_request($xml) {
            $ch = curl_init("https://onlinetools.ups.com/ups.app/xml/Rate");
            curl_setopt($ch, CURLOPT_HEADER, 1);
            curl_setopt($ch,CURLOPT_POST,1);
            curl_setopt($ch,CURLOPT_TIMEOUT, 60);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

            curl_setopt($ch,CURLOPT_POSTFIELDS, $xml);
            return curl_exec($ch);
        }

        private function parse_xml_resp($resp) {
            $data = strstr($resp, '<?');

            return strpos(strtolower($data), strtolower('Delivery Area Surcharge has been added to the service cost')) !== false;
        }

        private function get_customer_shipping_data() {

            // Get customer address
			$customer = WC()->session->get('customer');
			
            $postcode = ( isset( $customer['shipping_postcode'] ) ) ? $customer['shipping_postcode'] : 0;
            $company = ( isset( $customer['company'] ) ) ? $customer['company'] : '';
            $shipping_city = ( isset( $customer['shipping_city'] ) ) ? $customer['shipping_city'] : '';
            $shipping_country = ( isset( $customer['shipping_country'] ) ) ? $customer['shipping_country'] : '';
            $shipping_address_1 = ( isset( $customer['shipping_address_1'] ) ) ? $customer['shipping_address_1'] : '';
            $shipping_address_2 = ( isset( $customer['shipping_address_2'] ) ) ? $customer['shipping_address_2'] : '';

            $customer_address = array(
                'address'   => implode( '', array( $shipping_address_1, $shipping_address_2 ) ),
                'city'      => $shipping_city,
                'country'   => $shipping_country
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
    }