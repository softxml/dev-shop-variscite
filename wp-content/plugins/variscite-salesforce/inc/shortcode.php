<?php
class newsletterSFDCIntegrationShortcode {
	private $sfdc;
	private $alertCount;
	function __construct() {
		$this->sfdc = new newsletterSFDCIntegration();
		$this->alertCount = 3;
		
		add_shortcode( 'variscite-newsletter', array( $this, 'variscite__newsletter_shortcode' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'variscite__newsletter_shortcode_scripts' ) );
		add_action( 'wp_ajax_newsletter_form_feedback', array( $this, 'variscite__newsletter_feedback' ) );
		add_action( 'wp_ajax_nopriv_newsletter_form_feedback', array( $this, 'variscite__newsletter_feedback' ) );
		
		add_shortcode( 'variscite-contact-us', array( $this, 'variscite__contact_shortcode' ) );
		add_action( 'wp_ajax_contact_form_feedback', array( $this, 'variscite__contact_feedback' ) );
		add_action( 'wp_ajax_nopriv_contact_form_feedback', array( $this, 'variscite__contact_feedback' ) );
		
		add_action( 'wpcf7_before_send_mail', array( $this, 'contact_us_pass_pardot_data' ) );
		
		add_shortcode( 'variscite-newsletter-privacy', array($this, 'variscite__newsletter_privacy_shortcode' ) );
		
		add_action( 'rest_api_init', array( $this, 'register_sfdc_sync_api_endpoint' ) );
		
		//register custom cpt for plugin
		add_action( 'init', array( $this, 'register_custom_cpt' ) );
	}
	
	public function register_custom_cpt(){
		register_post_type( 'variscite-newsletter',
			array(
				'labels'      => array(
					'name'          => __( 'Newsletter Entries', 'textdomain' ),
					'singular_name' => __( 'Newsletter Entry', 'textdomain' ),
				),
				'public'      => true,
				'has_archive' => true,
				'supports'	  => array( 'title' ),
			)
		);
	}
	
	public function register_sfdc_sync_api_endpoint(){
		register_rest_route('variscite-sfdc/v1', '/sync', array(
			'methods'  => 'GET',
			'callback' => array( $this, 'sfdc_sync_api_endpoint_callback' ),
		));

		register_rest_route('variscite-sfdc/v1', '/sync', array(
			'methods'  => 'POST',
			'callback' => array( $this, 'sfdc_sync_api_endpoint_callback' ),
		));
	}
	
	public function sfdc_sync_api_endpoint_callback(){
		// Collect all of the leads with the 'pending' status
		/* $leads = new WP_Query(array(
			'post_type' => 'leads',
			'posts_per_page' => -1,
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key' => 'lead_record_sf',
					'value' => 'on',
					'compare' => '!='
				),
				array(
					'key' => 'lead_record_sf',
					'value' => '1',
					'compare' => 'NOT EXISTS'
				)
			),
			'date_query' => array(
				'after' => '2022-04-10',
			),
		));

		if( $leads->posts && !empty( $leads->posts ) ){
			foreach( $leads->posts as $lead ){
				$lid = $lead->ID;

				if ( get_field( "curl_errors_documentation", $lid ) ){
					continue;
				}
				
				$alert_count = (int)get_post_meta( $lid, 'alert_count', true );
				
				$sfdc_data = json_decode( get_field( 'sfdc_object_to_be_sent', $lid ), true);

				// Init the connection to the API and pass the lead to SFDC
				try {
					$records = array();
					
					$url = $this->sfdc->baseUrl."services/data/v56.0/sobjects/Lead/";
					$response = $this->sfdc->call_sfdc_api( $url, $sfdc_data, 'POST' );
					
					update_field( 'request_log', json_encode( $sfdc_data ), $lid );

					if( $response->success == true ){
						update_field('lead_record_sf', 'on', $lid);
					}else{
						if( !empty( $response ) ){
							update_field( 'curl_errors_documentation', 'Request failed: HTTP status code: ' . json_encode( $response ), $lid );
							
							if( $alert_count <= $this->alertCount ){
								sfalert_email( $lid );
								update_post_meta( $lid, 'alert_count', $alert_count+1 );
							}
						}else{
							$emptyTryCount = (int)get_post_meta( $lid, 'emptyResCount', true );
							if( $emptyTryCount == 5 ){
								sfalert_email( $lid );
							}else{
								update_post_meta( $lid, 'emptyResCount', $emptyTryCount+1 );
							}
						}
					}

				} catch (SoapFault $e) {

					# Catch and send out email to support if there is an error
					$errmessage =  "Exception ".$e->faultstring."<br/><br/>\n";
					
					update_field( 'curl_errors_documentation', json_encode($errmessage ), $lid );
					if( $alert_count <= $this->alertCount ){
						sfalert_email( $lid );
						update_post_meta( $lid, 'alert_count', $alert_count+1 );
					}

				} catch (Exception $e) {

					# Catch and send out email to support if there is an error
					$errmessage =  "Exception ".$e->faultstring."<br/><br/>\n";
					
					update_field( 'curl_errors_documentation', json_encode($errmessage ), $lid );
					if( $alert_count <= $this->alertCount ){
						sfalert_email( $lid );
						update_post_meta( $lid, 'alert_count', $alert_count+1 );
					}
				}

				// Send an email to the owners about the lead
				$settings = get_field( 'quote_settings', 'option' );
				$message  = get_field( 'email_message_to_be_sent', $lid );
				$subject  = get_field( 'email_subject_to_be_sent', $lid );

				$sendResult	= wp_mail( $settings['email_to'], $subject, $message );
				if( $sendResult ){ update_field( 'lead_record_email', 'on', $lid );  }
			}
		} */
				
		//run for newsletter
		$newsletterRecords = new WP_Query(array(
			'post_type' => 'variscite-newsletter',
			'posts_per_page' => -1,
			'meta_query' => array(
				'relation' => 'OR',
				array(
					'key' => 'newsletter_record_sf',
					'value' => 'on',
					'compare' => 'NOT LIKE'
				),
				array(
					'key' => 'newsletter_record_sf',
					'compare' => 'NOT EXISTS'
				),
			),
			'date_query' => array(
				'after' => '2024-04-17',
				'inclusive' => true
			),
		));
		if( $newsletterRecords->have_posts() ){
			while( $newsletterRecords->have_posts() ){
				$newsletterRecords->the_post();
				
				$post_id = get_the_ID();
				$formdata = get_field( 'newsletter_sfdc_object_to_be_sent' );
				$formdata = json_decode( $formdata, true );
				if( $formdata ){
					//Pass to SFDC
					$sfdc_resp = $this->sfdc->subscribe_to_newsletter( $formdata, $post_id );
					if( $sfdc_resp ){
						update_field( 'newsletter_record_sf', 'on', $post_id );
					}
				}
			}
		}
		wp_reset_postdata();
		
		wp_send_json_success();
		add_filter( 'auto_update_plugin', '__return_false' );
	}

	public function variscite__newsletter_shortcode_scripts(){
		wp_enqueue_script( 'newsletter-js', VARISCITE_URL.'/assets/js/newsletter.js', 'jquery', '', true );
		wp_localize_script(
			'newsletter-js',
			'varinews',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'mc_nonce'  => wp_create_nonce( 'mc-nonce' ),
                'mc_action' => 'update_mailchimp_user'
			)
		);
		
		wp_enqueue_script( 'recaptcha-js', 'https://www.google.com/recaptcha/api.js', '', '', true );
		
		wp_enqueue_script( 'form-js', VARISCITE_URL.'/assets/js/form.js', 'jquery', '', true );
		wp_localize_script(
			'form-js',
			'variform',
			array(
				'post_id'  => get_the_ID(),
				'ajax_url' => admin_url( 'admin-ajax.php' )
			)
		);
		
		wp_enqueue_script( 'global-ajaxfunc', VARISCITE_URL.'/assets/js/global-ajax.js', array( 'jquery' ) );
		wp_localize_script( 'global-ajaxfunc', 'ajax_object', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	}
	
	public function variscite__newsletter_privacy_shortcode( $atts = array(), $content = null ){
		ob_start();
		$class = isset($atts['class']) ? $atts['class'] : '';
		?>
		<div class="newsletter-form-wrapper <?php echo $class; ?>">
			<form class="newsletter-form privacy-policy-consent" method="post">
				<div class="newsletter-form-fields privacy-policy-nl">
					<p>
						<input type="text" name="firstname" placeholder="<?php _e('First Name', 'variscite'); ?>">
						<input type="text" name="lastname" placeholder="<?php _e('Last Name', 'variscite'); ?>">
					</p>
					<p>
						<input type="email" name="email" placeholder="<?php _e('Your email', 'variscite'); ?>">
					</p>
					<p class="privacy-wrapper">
						<label>
							<input name="privacy" type="checkbox" value="1"><span><?php printf(__('I agree to the Variscite %s', 'variscite'), '<a href="/privacy-policy/" target="_blank">' . __('Privacy Policy') . '</a>'); ?></span>
						</label>
					</p>

					<p class="privacy-consent-more-info">* For information about our privacy policy, please visit: <a href="/privacy-policy/">wordpress-689526-2938320.cloudwaysapps.com/privacy-policy</a>.<br/>* You will have the opportunity to withdraw your consent or opt-out of receiving communication from us every time we contact you or at any point by sending an email to <a href="mailto:sales@variscite.com">sales@variscite.com</a>.</p>

					<p class="submit-wrapper">
						<input type="submit" value="<?php _e('Sign up', 'variscite'); ?>">
					</p>
				</div>

				<label class="company-wrapper">
					Leave this field empty if you're human:
					<input type="text" name="company-name" value="" tabindex="-1" autocomplete="off">
				</label>
				<input type="hidden" name="country" val="" />
				<div class="newsletter-response privacy">
					<i class="fa fa-exclamation-triangle c6"></i>
				</div>
			</form>
			<div class="thank-you-message privacy-policy-ty"><?php _e( 'Thank you, your sign-up request was successful!', 'variscite' ); ?></div>
		</div>
		<?php
		$shortcode = ob_get_contents();
		ob_end_clean();
		return $shortcode;
	}
		
	public function variscite__newsletter_shortcode($atts = array(), $content = null) {
        ob_start();
		?>
		<div class="newsletter-form-wrapper">
			<form class="newsletter-form" method="post">
				<div class="newsletter-form-fields">
					<p>
						<input type="text" name="firstname" placeholder="<?php _e('First Name', 'variscite'); ?>">
						<input type="text" name="lastname" placeholder="<?php _e('Last Name', 'variscite'); ?>">
					</p>

					<p>
						<input type="email" name="email" placeholder="<?php _e('Your email', 'variscite'); ?>">
					</p>

					<p class="privacy-wrapper">
						<label>
							<input name="privacy" type="checkbox" value="1"><span><?php printf(__('I agree to the Variscite %s', 'variscite'), '<a href="https://www.variscite.com/privacy-policy/" target="_blank">' . __('Privacy Policy','variscite') . '</a>'); ?></span>
						</label>
					</p>

					<p class="submit-wrapper">
						<input type="submit" class="btn btn-arrow-01" value="<?php _e( 'Sign up', 'variscite' ); ?>" />
					</p>
				</div>

				<label class="company-wrapper">
					Leave this field empty if you're human:
					<input type="text" name="company-name" value="" tabindex="-1" autocomplete="off">
				</label>

				<input type="hidden" name="country" val="" />
				<div class="newsletter-response">
					<i class="fa fa-exclamation-triangle c6"></i><span></span>
				</div>
			</form>

			<div class="thank-you-message"><?php _e('Thank you, your sign-up request was successful!', 'variscite'); ?></div>
		</div>
		<?php
		$shortcode = ob_get_contents();
		ob_end_clean();

		return $shortcode;
	}

	public function variscite__newsletter_feedback(){

		$feedback = array(
			'result' => true,
			'notes' => ''
		);

		$form_data = $this->sanitize_form_data( $_POST['form_data'] );
		foreach( $form_data as $field_key => $field_val ){
			// Check required fields
			if( ( $field_key == 'firstname' || $field_key == 'lastname' || $field_key == 'email' ) && ( !$field_val || empty( $field_val ) ) ){
				$feedback['result'] = false;
				$feedback['notes'] = empty($feedback['notes']) ? 'All fields are required.' : $feedback['notes'];
			}

			// Validate email
			if( $field_key == 'email' && !empty( $field_val ) && ! filter_var($field_val ,FILTER_VALIDATE_EMAIL ) ){
				$feedback['result'] = false;
				$feedback['notes'] = empty( $feedback['notes'] ) ? 'Please use a valid email address.' : $feedback['notes'];
			}

			// Validate honeypot
			if( $field_key == 'company' && ! empty( $field_val ) ){
				$feedback['result'] = false;
				$feedback['notes'] = empty( $feedback['notes'] ) ? 'An error occurred. Please try again.' : $feedback['notes'];
			}
		}

		// Validate privacy policy
		if( !isset( $form_data['privacy'] ) || empty( $form_data['privacy'] ) ){
			$feedback['result'] = false;
			$feedback['notes'] = empty( $feedback['notes'] ) ? 'Please accept the privacy policy.' : $feedback['notes'];
		}

		if( $feedback['result'] ){
			// Set country name
			$form_data['country'] = $this->country_code_to_name( $form_data['country'] );

			// Set Privacy Policy date
			$form_data['privacy'] = date( 'Y-m-d\TH:i:s\Z' );

			// Unset irrelevant fields
			unset( $form_data[''] );
			unset( $form_data['company-name'] );
			
			$postArr = array(
                'post_title'    => __( 'New Entry From' ).': '.$form_data['firstname'].' '.$form_data['lastname'],
                'post_status'   => 'publish',
                'post_type'		=> 'variscite-newsletter'
            );
            $lid = wp_insert_post( $postArr, false, false );
			if( $lid ){
				//user info
				update_field( 'newsletter_firstname', $form_data['firstname'], $lid );
				update_field( 'newsletter_lastname', $form_data['lastname'], $lid );
				update_field( 'newsletter_email', $form_data['email'], $lid );
				update_field( 'newsletter_country', $form_data['country'], $lid );
				
				update_field( 'newsletter_created_record', 'on', $lid );
				
				update_field( 'newsletter_sfdc_object_to_be_sent', json_encode( $form_data ), $lid );
			}
			
			$sfdc_resp = true;
			die( json_encode( array(
				'result' => $sfdc_resp,
				'notes' => $sfdc_resp ? 'Thank you for subscribing!' : 'An error occurred. Please try again.'
			) ) );
		}

		die( json_encode( $feedback ) );
	}
	
	public function convert_symbols_str( $val ){
		$val = htmlspecialchars_decode( $val );
		$val = str_replace( '\r', '', $val );
		$val = str_replace( '\"', "'", $val );
		$val = str_replace( "\'", "'", $val );
		$val = str_replace( array( "&quot;", "\&quot;" ), '"', $val );
		$val = str_replace( array( "\&forall;", "\&part;", "\&exist;", "\&empty;", "\&nabla;", "\&isin;", "\&notin;", "\&ni;", "\&prod;", "\&sum;", "\&Alpha;", "\&Beta;", "\&Gamma;", "\&Delta;", "\&Epsilon;", "\&Zeta;", "\&copy;", "\&reg;", "\&euro;", "\&trade;", "\&larr;", "\&uarr;", "\&rarr;", "\&darr;", "\&spades;", "\&clubs;", "\&hearts;", "\&diams;", "\&lt;br&gt;", "\&lt;", "\&gt;", "&lt;", "&gt;", "&amp;" ), array( "∀", "∂", "∃", "∅", "∇", "∈", "∉", "∋", "∏", "∑", "Α", "Β", "Γ", "Δ", "Ε", "Ζ", "©", "®", "€", "™", "←", "↑", "→", "↓", "♠", "♣", "♥", "♦", " ", "<", ">", "<", ">", "&" ), $val );
		
		return $val;
	}
	
	private function sanitize_form_data( $data ){
		$data_array = array();

		foreach( $data as $datum ){
			$val = $datum['val'];			
			$data_array[esc_html($datum['key'])] = $this->convert_symbols_str( $val );
		}

		return $data_array;
	}

	private function country_code_to_name( $cc ){
		$countries = array(
			'AD' => 'Andorra',
			'AF' => 'Afghanistan',
			'AG' => 'Antigua and Barbuda',
			'AI' => 'Anguilla',
			'AL' => 'Albania',
			'AO' => 'Angola',
			'AQ' => 'Antarctica',
			'AR' => 'Argentina',
			'AT' => 'Austria',
			'AU' => 'Australia',
			'AW' => 'Aruba',
			'AX' => 'Aland Islands',
			'BA' => 'Bosnia and Herzegovina',
			'BB' => 'Barbados',
			'BD' => 'Bangladesh',
			'BE' => 'Belgium',
			'BF' => 'Burkina Faso',
			'BG' => 'Bulgaria',
			'BH' => 'Bahrain',
			'BI' => 'Burundi',
			'BJ' => 'Benin',
			'BL' => 'Saint Barthélemy',
			'BM' => 'Bermuda',
			'BN' => 'Brunei Darussalam',
			'BO' => 'Bolivia, Plurinational State of',
			'BQ' => 'Bonaire, Sint Eustatius and Saba',
			'BR' => 'Brazil',
			'BS' => 'Bahamas',
			'BT' => 'Bhutan',
			'BV' => 'Bouvet Island',
			'BW' => 'Botswana',
			'BZ' => 'Belize',
			'CA' => 'Canada',
			'CC' => 'Cocos (Keeling) Islands',
			'CD' => 'Congo, the Democratic Republic of the',
			'CF' => 'Central African Republic',
			'CG' => 'Congo',
			'CH' => 'Switzerland',
			'CI' => 'Cote d\'Ivoire',
			'CK' => 'Cook Islands',
			'CL' => 'Chile',
			'CM' => 'Cameroon',
			'CN' => 'China',
			'CO' => 'Colombia',
			'CR' => 'Costa Rica',
			'CV' => 'Cape Verde',
			'CW' => 'Curaçao',
			'CX' => 'Christmas Island',
			'CY' => 'Cyprus',
			'CZ' => 'Czech Republic',
			'DE' => 'Germany',
			'DJ' => 'Djibouti',
			'DK' => 'Denmark',
			'DM' => 'Dominica',
			'DO' => 'Dominican Republic',
			'DZ' => 'Algeria',
			'EC' => 'Ecuador',
			'EE' => 'Estonia',
			'EH' => 'Western Sahara',
			'ER' => 'Eritrea',
			'ES' => 'Spain',
			'ET' => 'Ethiopia',
			'FI' => 'Finland',
			'FJ' => 'Fiji',
			'FK' => 'Falkland Islands (Malvinas)',
			'FO' => 'Faroe Islands',
			'FR' => 'France',
			'GA' => 'Gabon',
			'GB' => 'United Kingdom',
			'GD' => 'Grenada',
			'GF' => 'French Guiana',
			'GG' => 'Guernsey',
			'GH' => 'Ghana',
			'GI' => 'Gibraltar',
			'GL' => 'Greenland',
			'GM' => 'Gambia',
			'GN' => 'Guinea',
			'GP' => 'Guadeloupe',
			'GQ' => 'Equatorial Guinea',
			'GR' => 'Greece',
			'GS' => 'South Georgia and the South Sandwich Islands',
			'GT' => 'Guatemala',
			'GW' => 'Guinea-Bissau',
			'GY' => 'Guyana',
			'HM' => 'Heard Island and McDonald Islands',
			'HN' => 'Honduras',
			'HR' => 'Croatia',
			'HT' => 'Haiti',
			'HU' => 'Hungary',
			'ID' => 'Indonesia',
			'IE' => 'Ireland',
			'IL' => 'Israel',
			'IM' => 'Isle of Man',
			'IN' => 'India',
			'IO' => 'British Indian Ocean Territory',
			'IS' => 'Iceland',
			'IT' => 'Italy',
			'JE' => 'Jersey',
			'JM' => 'Jamaica',
			'JP' => 'Japan',
			'KE' => 'Kenya',
			'KI' => 'Kiribati',
			'KM' => 'Comoros',
			'KN' => 'Saint Kitts and Nevis',
			'KR' => 'Korea, Republic of',
			'KW' => 'Kuwait',
			'KY' => 'Cayman Islands',
			'LA' => 'Lao People\'s Democratic Republic',
			'LC' => 'Saint Lucia',
			'LI' => 'Liechtenstein',
			'LK' => 'Sri Lanka',
			'LR' => 'Liberia',
			'LS' => 'Lesotho',
			'LT' => 'Lithuania',
			'LU' => 'Luxembourg',
			'LV' => 'Latvia',
			'MA' => 'Morocco',
			'MC' => 'Monaco',
			'ME' => 'Montenegro',
			'MF' => 'Saint Martin (French part)',
			'MG' => 'Madagascar',
			'MK' => 'Macedonia, the former Yugoslav Republic of',
			'ML' => 'Mali',
			'MM' => 'Myanmar',
			'MQ' => 'Martinique',
			'MR' => 'Mauritania',
			'MS' => 'Montserrat',
			'MT' => 'Malta',
			'MU' => 'Mauritius',
			'MV' => 'Maldives',
			'MW' => 'Malawi',
			'MX' => 'Mexico',
			'MY' => 'Malaysia',
			'MZ' => 'Mozambique',
			'NA' => 'Namibia',
			'NC' => 'New Caledonia',
			'NE' => 'Niger',
			'NF' => 'Norfolk Island',
			'NG' => 'Nigeria',
			'NI' => 'Nicaragua',
			'NL' => 'Netherlands',
			'NO' => 'Norway',
			'NP' => 'Nepal',
			'NR' => 'Nauru',
			'NU' => 'Niue',
			'NZ' => 'New Zealand',
			'OM' => 'Oman',
			'PA' => 'Panama',
			'PE' => 'Peru',
			'PF' => 'French Polynesia',
			'PG' => 'Papua New Guinea',
			'PH' => 'Philippines',
			'PK' => 'Pakistan',
			'PL' => 'Poland',
			'PM' => 'Saint Pierre and Miquelon',
			'PN' => 'Pitcairn',
			'PT' => 'Portugal',
			'PY' => 'Paraguay',
			'QA' => 'Qatar',
			'RE' => 'Reunion',
			'RO' => 'Romania',
			'RS' => 'Serbia',
			'RW' => 'Rwanda',
			'SB' => 'Solomon Islands',
			'SC' => 'Seychelles',
			'SE' => 'Sweden',
			'SG' => 'Singapore',
			'SH' => 'Saint Helena, Ascension and Tristan da Cunha',
			'SI' => 'Slovenia',
			'SJ' => 'Svalbard and Jan Mayen',
			'SK' => 'Slovakia',
			'SL' => 'Sierra Leone',
			'SM' => 'San Marino',
			'SN' => 'Senegal',
			'SO' => 'Somalia',
			'SR' => 'Suriname',
			'ST' => 'Sao Tome and Principe',
			'SV' => 'El Salvador',
			'SX' => 'Sint Maarten (Dutch part)',
			'SZ' => 'Swaziland',
			'TC' => 'Turks and Caicos Islands',
			'TD' => 'Chad',
			'TF' => 'French Southern Territories',
			'TG' => 'Togo',
			'TH' => 'Thailand',
			'TK' => 'Tokelau',
			'TL' => 'Timor-Leste',
			'TN' => 'Tunisia',
			'TO' => 'Tonga',
			'TR' => 'Turkey',
			'TT' => 'Trinidad and Tobago',
			'TV' => 'Tuvalu',
			'TW' => 'Taiwan',
			'TZ' => 'Tanzania, United Republic of',
			'UG' => 'Uganda',
			'US' => 'United States',
			'UY' => 'Uruguay',
			'VA' => 'Holy See (Vatican City State)',
			'VC' => 'Saint Vincent and the Grenadines',
			'VE' => 'Venezuela, Bolivarian Republic of',
			'VG' => 'Virgin Islands, British',
			'VN' => 'Viet Nam',
			'VU' => 'Vanuatu',
			'WF' => 'Wallis and Futuna',
			'WS' => 'Samoa',
			'YE' => 'Yemen',
			'YT' => 'Mayotte',
			'ZA' => 'South Africa',
			'ZM' => 'Zambia',
			'ZW' => 'Zimbabwe'
		);

		return $countries[strtoupper($cc)];
	}
	
	public function variscite__contact_shortcode( $atts = array(), $content = null ){
		ob_start();
		if( is_singular( 'product' ) ){
			$id = 'option';
		}else{
			$id = get_the_ID();
		}

		$fields = get_field( 'vari__contact-fields', $id );
		$placeholder = '';
        ?>
		<form class="vari-contact-form" data-redirect="<?php echo get_field('vari_contact-redirect', $id); ?>">

			<?php foreach($fields as $field): ?>

				<fieldset style="<?php echo ($field['vari_field-type'] == 'hidden' ? 'margin: 0;' : ''); ?>" <?php if ($field['vari_field-type'] == 'select' && $field['vari_field-sfdc_id'] !== 'System__c' ) : echo 'class="select-wrap"'; elseif ($field['vari_field-type'] == 'select' && $field['vari_field-sfdc_id'] == 'System__c') : echo 'class="select-wrap som-multiselect"'; endif; ?>>
					<?php
					if(
						($field['vari_field-type'] !== 'select' || get_page_template_slug() == 'page-templates/contact-us.php') &&
						$field['vari_field-type'] !== 'hidden' && $field['vari_field-type'] !== 'checkbox'
					):
						$placeholder = $field['vari_field-label'];
					endif;
					?>

					<?php if($field['vari_field-type'] == 'select' && $field['vari_field-sfdc_id'] == 'System__c') : ?>
					<div class="selectBox" onclick="showCheckboxes()">
						<?php endif; ?>

						<<?php echo ($field['vari_field-type'] == 'textarea' ? 'textarea' : ($field['vari_field-type'] == 'select' ? 'select' : 'input')); ?>
						<?php echo ($field['vari_field-type'] !== 'textarea' ? 'type="' . $field['vari_field-type'] . '"' : ''); ?>
						name="<?php echo $field['vari_field-sfdc_id']; ?>"
						id="<?php echo $field['vari_field-sfdc_id']; ?>"
						placeholder="<?php echo $placeholder; ?>"
						class="<?php echo ($field['vari_field-required'] ? 'is-required' : ''); ?>"
						<?php
							if( is_singular( 'product' ) ){
								if( $field['vari_field-sfdc_id'] == 'Product_page__c' ){
									echo 'value="' . get_field('variscite__product_product_page_c' ).'"';
								}else if( $field['vari_field-sfdc_id'] == 'Processor__c' ){
									echo 'value="' . get_the_title().'"';
								}
							}
						?>
						<?php echo ( $field['vari_field-sfdc_id'] == 'Note__c' ) ? 'maxlength="2000"' : ''; ?>
						>

						<?php if($field['vari_field-type'] == 'checkbox') : ?>
							<label for="<?php echo $field['vari_field-sfdc_id']; ?>"><?php echo $field['vari_field-label']; ?></label>
						<?php endif; ?>

						<?php if($field['vari_field-type'] == 'select') : ?>

							<option selected value=""><?php echo $field['vari_field-label']; ?></option>

							<?php
							if($field['vari_field-sfdc_id'] !== 'System__c') :

								foreach(explode(PHP_EOL, $field['vari_field-select-options']) as $option):
									$key_val = explode(' : ', $option);
									?>

									<option value="<?php echo $key_val[0]; ?>"><?php echo $key_val[1]; ?></option>

								<?php
								endforeach;
							endif;
						endif;
						?>

						<?php if($field['vari_field-type'] == 'select' || $field['vari_field-type'] == 'textarea'): ?>
					</<?php echo $field['vari_field-type']; ?>>
				<?php endif; ?>

					<?php if($field['vari_field-type'] == 'select' && $field['vari_field-sfdc_id'] == 'System__c') : ?>
						<div class="overSelect"></div>
						</div>
						<div id="som-checkboxes">
							<?php
							foreach(explode(PHP_EOL, $field['vari_field-select-options']) as $option):
								$key_val = explode(' : ', $option);
								$opt_value = trim($key_val[1]); ?>
								<div class="form-group">
									<div class="multi-checkbox" data-system="<?php echo $opt_value; ?>"><?php echo $opt_value; ?></div>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>

					<span class="vari-error"></span>

				</fieldset>

			<?php endforeach; ?>

			<input type="submit" value="<?php echo get_field('vari_contact-submit_label', $id); ?>" />
		</form>

		<script type="text/javascript">
			var expanded = false;

			function showCheckboxes() {
				var checkboxes = document.getElementById("som-checkboxes");
				if (!expanded) {
					checkboxes.style.display = "block";
					expanded = true;
				} else {
					checkboxes.style.display = "none";
					expanded = false;
				}
			}

			jQuery(function($) {
				if($('body').is('.contact-us-page')) {
					document.addEventListener("click", function(event) {
						if (event.target.closest(".som-multiselect")) return;

						document.getElementById("som-checkboxes").style.display = "none";
						expanded = false;
					});


					if($('#som-checkboxes').length > 0) {
						var sList = "",
							sVal = "";
						$('#som-checkboxes .multi-checkbox').click(function() {
							$(this).toggleClass('checked');
							sList = '';
							$('#som-checkboxes .multi-checkbox').each(function() {
								sList += ($(this).hasClass('checked') ? $(this).attr('data-system') + ", " : "");
							});
							sVal = (sList !== '') ? sList.substring(0, sList.length - 2) : "";

							$('.selectBox option:selected').attr('value', sVal);
							$('.selectBox select').change();
						});
					}
				}
			});
		</script>
        <?php
		$shortcode = preg_replace('/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/', '', ob_get_contents());
		$shortcode = preg_replace('/\s+/', ' ', $shortcode);
		$shortcode = str_replace(" </textarea>", "</textarea>", $shortcode);

		ob_end_clean();

		return $shortcode;
	}

	public function variscite__contact_feedback(){
		$feedback = array(
			'result' => true,
			'notes' => ''
		);

		$form_data = $this->sanitize_form_data( $_POST['form_data'] );
		foreach( $form_data as $field_key => $field_val ){

			// Check required fields
			if(
				($field_key == 'FirstName' || $field_key == 'LastName' || $field_key == 'Email' || $field_key == 'Phone' || $field_key == 'Company') &&
				(! $field_val || empty($field_val))
			) {
				$feedback['result'] = false;

				$feedback['notes'][] = array(
					$field_key,
					'This field is required.'
				);
			}

			// Validate email
			if( $field_key == 'Email' && ! empty( $field_val ) && ! filter_var($field_val, FILTER_VALIDATE_EMAIL ) ){
				$feedback['result'] = false;

				$feedback['notes'][] = array(
					$field_key,
					'Please use a valid email address.'
				);
			}
		}
			
		// Set Privacy Policy date
		$form_data['Privacy_Policy__c'] = date('Y-m-d\TH:i:s\Z');

		// Pass to SFDC
		$sfdc_resp = $this->sfdc->pass_lead_to_sfdc( $form_data );

		// Log the lead in the DB
		$postid = wp_insert_post( array(
			'post_type'     => 'form-lead',
			'post_title'    =>  $form_data['FirstName'] . ' ' . $form_data['LastName'] . ' ' . date('d/m/Y'),
			'post_content'  => '',
			'post_status'   => 'publish',
			'post_author'   => 1
		) );
		
		foreach( $form_data as $key => $value ){
			update_field( 'variscite__leads-' . $key, $value, $postid );
		}
		
		update_field( 'lead_initiator', 'Contact Us', $postid );
		
		// Dispatch en email on successful lead submission
		if( $sfdc_resp['success'] ){
			$item = get_post(esc_html($_POST['post_id']));
			$item_id = $item->ID;

			if( get_post_type($item) == 'product' ){
				$item_id = 'option';
			}

			$to = get_field( 'vari_contact-email--addresses', $item_id );
			$subject = get_field( 'vari_contact-email--subject', $item_id );
			$body = get_field( 'vari_contact-email--body', $item_id );
			
			foreach( $form_data as $key => $value ){
				$subject = str_replace( '{' . $key . '}', $value, $subject );
				$body = str_replace( '{' . $key . '}', $value, $body );
				
				if( $key == "Page_url__c" ){
					$body = str_replace( '{Product_page__c}', $value, $body );
				}
			}

			$mail = wp_mail( $to, $subject, $body, array(
				'Content-Type: text/html; charset=UTF-8'
			) );
		
			// Update the SFDC and email fields
			update_field( 'variscite__leads-sfdc', 1, $postid);

			if( $mail ){
				update_field( 'variscite__leads-email-sent', 1, $postid );
			}
		}

		// Update the SFDC response
		update_field('variscite__leads-sfdc-resp', $sfdc_resp['message'], $postid);

		die(json_encode(array(
			'result' => $sfdc_resp,
			'notes' => $sfdc_resp ? 'success' : 'An error occurred. Please try again.'
		)));
		die( json_encode( $feedback ) );
	}
	
	// Contact page: send data to Pardot on CF7 submission
	function contact_us_pass_pardot_data() {

		// Get current form & submission and it's data
		$wpcf = WPCF7_ContactForm::get_current();
		$submission = WPCF7_Submission::get_instance();

		if($submission) {
			$posted_data = $submission->get_posted_data();

			$sfdc_url = 'https://webto.salesforce.com/servlet/servlet.WebToLead';
			$sfdc_fields = array(
				'oid' => '00D24000000I9Kc',
				'lead_source' => 'Web - contact via store',
				'first_name' => $this->convert_symbols_str( htmlspecialchars( $posted_data['first-name'] ) ),
				'last_name' => $this->convert_symbols_str( htmlspecialchars( $posted_data['last-name'] ) ),
				'company' => $this->convert_symbols_str( htmlspecialchars( $posted_data['company-name'] ) ),
				'email' => $this->convert_symbols_str( htmlspecialchars( $posted_data['email'] ) ),
				'phone' => $this->convert_symbols_str( htmlspecialchars( $posted_data['phone'] ) ),
				'country' => $this->convert_symbols_str( htmlspecialchars( $posted_data['country-name'] ) ),
				'00N24000004Cp7X' => $this->convert_symbols_str( htmlspecialchars( $posted_data['message'] ) ) // Note__c
			);

			// Privacy policy
			if(! empty($posted_data['privacy-policy'][0]) && isset($posted_data['privacy-policy'][0])) {
				$sfdc_fields['00N1p00000JVK3Y'] = htmlspecialchars($posted_data['privacy-policy'][0]);
			}

			$ch = curl_init();

			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch,CURLOPT_POST,1);
			curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

			curl_setopt($ch, CURLOPT_URL, $sfdc_url);
			curl_setopt($ch, CURLOPT_POST, count($sfdc_fields));
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($sfdc_fields));

			$result = curl_exec($ch);

			curl_close($ch);
			
			$f = fopen( ABSPATH."sf-call-cf7-debug.log", "a+" );
			fwrite( $f, '-----'.date( 'Y-m-d H:i:s' ).'-----' );
			fwrite( $f, PHP_EOL );
			fwrite( $f, print_r( $sfdc_fields, true ) );
			fwrite( $f, PHP_EOL );
			fwrite( $f, print_r( $result, true ) );
			fwrite( $f, PHP_EOL );
			fwrite( $f, PHP_EOL );
			fclose( $f );
		}
	}
}