<?php

    class newsletterSFDCIntegrationShortcode {

        private $sfdc;

        function __construct() {
            $this->sfdc = new newsletterSFDCIntegration();

            add_shortcode('variscite-newsletter', array($this, 'variscite__newsletter_shortcode'));
            add_action('wp_enqueue_scripts', array($this, 'variscite__newsletter_shortcode_scripts'));
            add_action('wp_ajax_newsletter_form_feedback', array($this, 'variscite__newsletter_feedback'));
            add_action('wp_ajax_nopriv_newsletter_form_feedback', array($this, 'variscite__newsletter_feedback'));
        }

        public function variscite__newsletter_shortcode_scripts() {
            wp_enqueue_script('newsletter-js', plugin_dir_url(__FILE__) . 'newsletter.js', 'jquery', '', true);
            wp_localize_script(
                'newsletter-js',
                'varinews',
                array(
                    'ajax_url' => admin_url('admin-ajax.php')
                )
            );
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
                            <input type="submit" value="<?php _e('Sign up', 'variscite'); ?>">
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

        public function variscite__newsletter_feedback() {

            $feedback = array(
                'result' => true,
                'notes' => ''
            );

            $form_data = $this->sanitize_form_data($_POST['form_data']);

            foreach($form_data as $field_key => $field_val) {

                // Check required fields
                if(
                    ($field_key == 'firstname' || $field_key == 'lastname' || $field_key == 'email') &&
                    (! $field_val || empty($field_val))
                ) {
                    $feedback['result'] = false;
                    $feedback['notes'] = empty($feedback['notes']) ? 'All fields are required.' : $feedback['notes'];
                }

                // Validate email
                if($field_key == 'email' && ! empty($field_val) && ! filter_var($field_val ,FILTER_VALIDATE_EMAIL)) {
                    $feedback['result'] = false;
                    $feedback['notes'] = empty($feedback['notes']) ? 'Please use a valid email address.' : $feedback['notes'];
                }

                // Validate honeypot
                if($field_key == 'company' && ! empty($field_val)) {
                    $feedback['result'] = false;
                    $feedback['notes'] = empty($feedback['notes']) ? 'An error occurred. Please try again.' : $feedback['notes'];
                }
            }

            // Validate privacy policy
            if(! isset($form_data['privacy']) || empty($form_data['privacy'])) {
                $feedback['result'] = false;
                $feedback['notes'] = empty($feedback['notes']) ? 'Please accept the privacy policy.' : $feedback['notes'];
            }

            if($feedback['result']) {

                // Set country name
                $form_data['country'] = $this->country_code_to_name($form_data['country']);

                // Set Privacy Policy date
                $form_data['privacy'] = date('Y-m-d\TH:i:s\Z');

                // Unset irrelevant fields
                unset($form_data['']);
                unset($form_data['company-name']);

                // Pass to SFDC
                $sfdc_resp = $this->sfdc->subscribe_to_newsletter($form_data);

                die(json_encode(array(
                    'result' => $sfdc_resp,
                    'notes' => $sfdc_resp ? 'Thank you for subscribing!' : 'An error occurred. Please try again.'
                )));
            }

            die(json_encode($feedback));
        }

        private function sanitize_form_data($data) {
            $data_array = array();

            foreach($data as $datum) {
                $data_array[esc_html($datum['key'])] = esc_html($datum['val']);
            }

            return $data_array;
        }

        private function country_code_to_name($cc) {

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
    }