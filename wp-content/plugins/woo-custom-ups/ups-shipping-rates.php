<?php

    class varisciteUPSShipping {

        private $ups_id;
        private $ups_pass;
        private $ups_access_key;

        private $dimensions;

        function __construct() {

            $this->shipper = get_field('woo_ups__shipper_id', 'option');
            $this->ups_id = get_field('woo_ups__user_id', 'option');
            $this->ups_pass = get_field('woo_ups__user_password', 'option');
            $this->ups_access_key = get_field('woo_ups__access_key', 'option');

//            $dimensions = get_field('variscite_shipping__pack_config', 'option');
//
//            $this->dimensions = array();
//
//            foreach($dimensions as $dimension) {
//
//                $this->dimensions[$dimension['p_num']] = array(
//                    'weight' => $dimension['p_weight'],
//                    'width' => $dimension['p_width'],
//                    'height' => $dimension['p_height'],
//                    'depth' => $dimension['p_depth']
//                );
//            }

            $this->dimensions = array(
                '01' => array( 'weight' => '5.5', 'width' => '34', 'height' => '23', 'depth' => '32'),
                '02' => array( 'weight' => '2', 'width' => '40', 'height' => '8', 'depth' => '32'),
                '03' => array( 'weight' => '1', 'width' => '26', 'height' => '8', 'depth' => '20'),
                '04' => array( 'weight' => '1', 'width' => '25', 'height' => '8', 'depth' => '18'),
                '05' => array( 'weight' => '1.5', 'width' => '23', 'height' => '16', 'depth' => '18'),
                '06' => array( 'weight' => '6.5', 'width' => '40', 'height' => '26', 'depth' => '32'),
                '07' => array( 'weight' => '13.5', 'width' => '64', 'height' => '34', 'depth' => '32'),
                '08' => array( 'weight' => '3', 'width' => '34', 'height' => '12', 'depth' => '32'),
                '09' => array( 'weight' => '3', 'width' => '34', 'height' => '12', 'depth' => '23'),
                '10' => array( 'weight' => '1', 'width' => '21', 'height' => '8', 'depth' => '20'),
                '11' => array( 'weight' => '1.5', 'width' => '22', 'height' => '14', 'depth' => '19'),
                '12' => array( 'weight' => '4', 'width' => '34', 'height' => '22', 'depth' => '23')
            );
        }

        public function get_shipping_rates($packs, $shipping_data) {

            $from_address = $shipping_data['from'];
            $to_address = $shipping_data['to'];

            $ups_xml = $this->construct_ups_xml($packs, $from_address, $to_address, $shipping_data);
            $ups_resp_xml = $this->execute_ups_request($ups_xml);
            $ups_resp = $this->parse_xml_resp($ups_resp_xml);

            // Log the call and response
            WC()->session->set('ups_raw_request', $ups_xml);
            WC()->session->set('ups_response', $ups_resp_xml);

            if($ups_resp && ! empty($ups_resp)) {
                return $ups_resp;
            }

            return false;
        }

        private function construct_ups_xml($packs, $from, $to, $ups_data) {

            $data = $this->open_xml_request();
            $data .= $this->shipment_xml_info($from, $to);

            foreach($packs['packages'] as $package_type => $amount) {
                $package_type_name = strlen($package_type) == 1 ? ('0' . $package_type) : $package_type;
                $data .= $this->shipment_xml_packages($this->dimensions[$package_type_name], $amount);
            }

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

        private function shipment_xml_packages($dimensions, $amount) {

            $data = '';

            for($i = 0; $i < $amount; $i++) {

                $data .= '<Package>
                    <PackagingType>
                        <Code>02</Code>
                    </PackagingType>

                    <Dimensions>
                        <UnitOfMeasurement>
                            <Code>CM</Code>
                        </UnitOfMeasurement>

                        <Length>' . $dimensions['depth'] . '</Length>
                        <Width>' . $dimensions['width'] . '</Width>
                        <Height>' . $dimensions['height'] . '</Height>
                    </Dimensions>

                    <PackageWeight>
                        <UnitOfMeasurement>
                            <Code>KGS</Code>
                        </UnitOfMeasurement>

                        <Weight>' . $dimensions['weight'] . '</Weight>
                    </PackageWeight>
                </Package>' . "\n";
            }

            return $data;
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

            $xml_parser = xml_parser_create();
            xml_parse_into_struct($xml_parser, $data, $vals, $index);
            xml_parser_free($xml_parser);

            $params = array();
            $level = array();

            foreach ($vals as $xml_elem) {

                if ($xml_elem['type'] == 'open') {

                    if (array_key_exists('attributes', $xml_elem)) {
                        list($level[$xml_elem['level']], $extra) = array_values($xml_elem['attributes']);
                    } else {
                        $level[$xml_elem['level']] = $xml_elem['tag'];
                    }
                }

                if ($xml_elem['type'] == 'complete') {
                    $start_level = 1;
                    $php_stmt = '$params';

                    while ($start_level < $xml_elem['level']) {
                        $php_stmt .= '[$level[' . $start_level . ']]';
                        $start_level++;
                    }

                    $php_stmt .= '[$xml_elem[\'tag\']] = $xml_elem[\'value\'];';
                    eval($php_stmt);
                }
            }

            return $params['RATINGSERVICESELECTIONRESPONSE']['RATEDSHIPMENT']['TOTALCHARGES']['MONETARYVALUE'];
        }
    }