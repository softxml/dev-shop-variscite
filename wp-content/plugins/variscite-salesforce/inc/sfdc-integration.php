<?php
class newsletterSFDCIntegration{
	private $sfdc_username;
	private $sfdc_password;
	private $sfdc_client_id;
	private $sfdc_client_secret;
	public $baseUrl;
	private $mail_headers;
	public $integrationError;
	function __construct(){
		$this->integrationError = '';
		
		$settings = get_option( 'variscite_sfdc_settings' );
		$sfdc_uname = ( isset( $settings['variscite_sfdc_username'] ) ) ? $settings['variscite_sfdc_username'] : '';
		$sfdc_pass = ( isset( $settings['variscite_sfdc_password'] ) ) ? $settings['variscite_sfdc_password'] : '';
		$sfdc_client_id = ( isset( $settings['variscite_sfdc_client_id'] ) ) ? $settings['variscite_sfdc_client_id'] : '';
		$sfdc_client_secret = ( isset( $settings['variscite_sfdc_client_secret'] ) ) ? $settings['variscite_sfdc_client_secret'] : '';
		$sfdc_sfdc_url = ( isset( $settings['variscite_sfdc_url'] ) ) ? $settings['variscite_sfdc_url'] : '';
		
		$sfdc_pass = vari_decrypt_data( $sfdc_pass );
		$sfdc_client_id = vari_decrypt_data( $sfdc_client_id );
		$sfdc_client_secret = vari_decrypt_data( $sfdc_client_secret );
		
		# SFDC Auth Information
		$this->sfdc_username = $sfdc_uname;
		$this->sfdc_password = $sfdc_pass;
		$this->sfdc_client_id = $sfdc_client_id;
		$this->sfdc_client_secret = $sfdc_client_secret;
		$this->baseUrl = $sfdc_sfdc_url;	
		
		$this->admin_email = array( get_option('admin_email'), 'lena.g@variscite.com', 'eden.d@variscite.com', 'roi@designercoded.com','allonsacks@gmail.com', 'michal@designercoded.com', 'avihu.h@variscite.com' );
		
		$this->mail_headers = array('Content-Type: text/html; charset=UTF-8');
	}
	
	function get_error(){
		return $this->integrationError;
	}
	
	function generate_access_token(){
		$url = $this->baseUrl."services/oauth2/token";
		$data = array(
			'grant_type'	=>	'password',
			'client_id'		=>	$this->sfdc_client_id,
			'client_secret'	=>	$this->sfdc_client_secret,
			'username'		=>	$this->sfdc_username,
			'password'		=>	$this->sfdc_password
		);
		$response = wp_remote_post( $url, array(
		    'body'    => $data,
		) );
		
		$f = fopen( ABSPATH."sf-generate-access_token.log", "a+" );
		fwrite( $f, '-----'.date( 'Y-m-d H:i:s' ).'-----' );
		fwrite( $f, PHP_EOL );
		fwrite( $f, print_r( wp_remote_retrieve_body( $response ), true ) );
		fwrite( $f, PHP_EOL );
		fwrite( $f, PHP_EOL );
		fclose( $f );

		if( is_wp_error( $response ) ){
			return false;
		}else{
			$response = wp_remote_retrieve_body( $response );
			$response = json_decode( $response );
			if( isset( $response->access_token ) && !empty( $response->access_token ) ){
				return $response->access_token;
			}else{
				$this->integrationError = json_encode( $response );
				return false;
			}
		}
		return false;
	}
	
	public function call_sfdc_api( $url, $data, $method = 'POST' ){
		$access_token = $this->generate_access_token();
		$access_token_empty = '';
		if( $access_token ){
			$curl = curl_init();
			curl_setopt_array( $curl, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => $method,
				CURLOPT_POSTFIELDS => json_encode( $data ),
				CURLOPT_HTTPHEADER => array(
					'Authorization: Bearer '.$access_token,
					'Content-Type: application/json',
				),
			));
			$access_token_empty = 'access token - '.$access_token;
			$apiResponse = curl_exec( $curl );
			curl_close( $curl );
		}else{
			$access_token_empty = 'access token empty';
			$apiResponse = $access_token;
		}
		
		if( isset( $data['leadSource'] ) && $data['leadSource'] == "Woocommerce" ){
			$initiator = 'Woo order place';
		}else if( isset( $data['Payment_approval__c'] ) ){
			$initiator = 'Woo order payment update';
		}else if( isset( $data['Privacy_Policy__c'] ) ){
			$initiator = 'privacy policy update';
		}else if( is_user_logged_in() ){
			$user_info = get_userdata( get_current_user_id() );
			$initiator = $user_info->first_name.' '.$user_info->last_name;
		}else{
			$initiator = 'Cron';
		}
		
		$f = fopen( ABSPATH."sf-call-debug.log", "a+" );
		fwrite( $f, '-----'.date( 'Y-m-d H:i:s' ).'-----' );
		fwrite( $f, PHP_EOL );
		fwrite( $f, 'Initiator - '.$initiator );
		fwrite( $f, PHP_EOL );
		fwrite( $f, print_r( $access_token_empty, true ) );
		fwrite( $f, PHP_EOL );
		fwrite( $f, print_r( $data, true ) );
		fwrite( $f, PHP_EOL );
		fwrite( $f, print_r( $apiResponse, true ) );
		fwrite( $f, PHP_EOL );
		fwrite( $f, PHP_EOL );
		fclose( $f );
		
		$apiResponse = json_decode( $apiResponse );
		return $apiResponse;
	}
	
	public function subscribe_to_newsletter( $data, $post_id = 0 ){
		if( $post_id ){
			update_field( 'lead_initiator', 'subscribe_to_newsletter', $post_id );
		}
		// Get both leads and contacts from SFDC with the privacy field empty,
		// that use the specified email.
		$objects = $this->sfdc_get_contacts_and_leads( false, $data['email'] );
		// (2) If there are existing records, update their privacy policy field value
		if( $objects && ! empty( $objects ) ){

			// (2.1) Check if there are records without the Privacy Policy field filled and fill it for them,
			// if there are none - don't do anything.
			$empty_objects = $this->sfdc_get_contacts_and_leads( true, $data['email'] );
						
			if( $empty_objects && !empty( $empty_objects ) ){
				if( isset( $empty_objects['contacts'] ) ){
					foreach( $empty_objects['contacts'] as $contact ){
						$id = $contact->Id;
						$url = $this->baseUrl."services/data/v56.0/sobjects/Contact/".$id;
						$dataArr = array(
							'Privacy_Policy__c'	=>	$data['privacy']
						);
						$apiResponse = $this->call_sfdc_api( $url, $dataArr, 'PATCH' );
						if( $post_id ){
							update_field( 'newsletter_curl_errors_documentation', print_r( $apiResponse, true ), $post_id );
						}
					}
				}
				if( isset( $empty_objects['leads'] ) ){
					foreach( $empty_objects['leads'] as $lead ){
						$id = $lead->Id;
						$url = $this->baseUrl."services/data/v56.0/sobjects/Lead/".$id;
						$dataArr = array(
							'Privacy_Policy__c'	=>	$data['privacy']
						);
						$apiResponse = $this->call_sfdc_api( $url, $dataArr, 'PATCH' );
						if( $post_id ){
							update_field( 'newsletter_curl_errors_documentation', print_r( $apiResponse, true ), $post_id );
						}
						/* $f = fopen( ABSPATH."sf-ddebug.log", "a+" );
						fwrite( $f, '-----'.date( 'Y-m-d H:i:s' ).'-----' );
						fwrite( $f, PHP_EOL );
						fwrite( $f, $data['email'] );
						fwrite( $f, PHP_EOL );
						fwrite( $f, print_r( $r, true ) );
						fwrite( $f, PHP_EOL );
						fwrite( $f, PHP_EOL );
						fclose( $f ); */
					}
				}
			}
			return true;
		}else{ // (3) If there are none, create a new contact under the Newsletter account
			$url = $this->baseUrl."services/data/v56.0/sobjects/Contact/";
			$data = array(
				'FirstName' => $data['firstname'],
				'LastName' => $data['lastname'],
				'Email' => $data['email'],
				'MailingCountry' => $data['country'],
				'Privacy_Policy__c' => $data['privacy'],
				'AccountId' => '0011p00002gBOqMAAW',
				'LeadSource' => 'Newsletter'
			);
			$apiResponse = $this->call_sfdc_api( $url, $data, 'POST' );
			if( $post_id ){
				update_field( 'newsletter_curl_errors_documentation', print_r( $apiResponse, true ), $post_id );
			}
			return $apiResponse->success;
		}
	}

	public function sfdc_get_contacts_and_leads( $with_privacy_policy, $email ){
		$objects = array();

		$query = "SELECT Id, Privacy_Policy__c FROM Contact WHERE email='" . $email . "'" . ($with_privacy_policy ? " AND Privacy_Policy__c=null" : "");
		$searchQuery = $this->baseUrl."services/data/v56.0/query/?q=".urlencode( $query );

		$apiResponse = $this->call_sfdc_api( $searchQuery, array(), 'GET' );
		if( isset( $apiResponse->done ) && $apiResponse->done == true ){
			foreach( $apiResponse->records as $contact ){
				$objects['contacts'][] = $contact;
			}
		}

		$query = "SELECT Id, Privacy_Policy__c FROM Lead WHERE email='" . $email . "'" . ($with_privacy_policy ? " AND Privacy_Policy__c=null" : "");
		$searchQuery = $this->baseUrl."services/data/v56.0/query/?q=".urlencode( $query );

		$apiResponse = $this->call_sfdc_api( $searchQuery, array(), 'GET' );
		if( isset( $apiResponse->done ) && $apiResponse->done == true ){
			foreach( $apiResponse->records as $lead ){
				$objects['leads'][] = $lead;
			}
		}

		return $objects;
	}
	
	public function pass_lead_to_sfdc( $data ){
        try{
			$url = $this->baseUrl."services/data/v56.0/sobjects/Lead/";
			$data['LeadSource'] = __( 'Web - contact via store', 'variscite' );
			$response = $this->call_sfdc_api( $url, $data, 'POST' );
            if( $response->success == true ){
                return array(
                    'success' => $response->success,
                    'message' => print_r( $response, true )
                );
            }else{
				$log = '';
				$log .= 'Site - '.site_url();
				$log .= '<br />';
				$log .= 'Initiator - Contact Form';
				$log .= PHP_EOL;
				if( isset( $data['Email'] ) ){
					$log .= 'Email - '.$data['Email'];
					$log .= PHP_EOL;
				}
				$log .= print_r( $response, true );
				$log .= '<br />';
				
                wp_mail( $this->admin_email, "Variscite Store: Contact Form Salesforce Integration Failure", $log. "\n" . json_encode( $data ), $this->mail_headers );

                return array(
                    'success' => false,
                    'message' => print_r( $response, true )
                );
            }

        }catch( Exception $e ){
            # Catch and send out email to support if there is an error
            $errmessage =  "Exception ".$e->faultstring."<br/><br/>\n";
            
			$log = '';
			$log .= 'Site - '.site_url();
			$log .= '<br />';
			$log .= 'Initiator - Contact Form';
			$log .= PHP_EOL;
			if( isset( $data['Email'] ) ){
				$log .= 'Email - '.$data['Email'];
				$log .= PHP_EOL;
			}
			$log .= print_r( $errmessage, true );
			$log .= '<br />';
					
            wp_mail( $this->admin_email, "Variscite Store: Contact Form Salesforce Integration Failure", $log, $this->mail_headers );

            return array(
                'success' => false,
                'message' => print_r( $errmessage, true )
            );
        }

        return array(
            'success' => false,
            'message' => 'Failed to init SFDC integration'
        );
    }
}