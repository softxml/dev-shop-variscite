<?php

    class contactSFDCIntegrationShortcode {

        private $sfdc;

        function __construct() {
            $this->sfdc = new contactSFDCIntegration();

            add_shortcode('variscite-contact-us', array($this, 'variscite__contact_shortcode'));
            add_action('wp_enqueue_scripts', array($this, 'variscite__contact_shortcode_scripts'));
            add_action('wp_ajax_contact_form_feedback', array($this, 'variscite__contact_feedback'));
            add_action('wp_ajax_nopriv_contact_form_feedback', array($this, 'variscite__contact_feedback'));
        }

        public function variscite__contact_shortcode_scripts() {

            wp_enqueue_script('recaptcha-js', 'https://www.google.com/recaptcha/api.js', '', '', true);

            wp_enqueue_script('form-js', plugin_dir_url(__FILE__) . 'form.js', 'jquery', '', true);
            wp_localize_script(
                'form-js',
                'variform',
                array(
                    'post_id'  => get_the_ID(),
                    'ajax_url' => admin_url('admin-ajax.php')
                )
            );
        }

        public function variscite__contact_shortcode($atts = array(), $content = null) {
            ob_start();

            if(is_singular('product')) {
                $id = 'option';
            } else {
                $id = get_the_ID();
            }

            $fields = get_field('vari__contact-fields', $id);
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
                            <?php echo (is_singular('product') && $field['vari_field-sfdc_id'] == 'Product_page__c' ? ('value="' . get_field('variscite__product_product_page_c')) . '"' : ''); ?>
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

        public function variscite__contact_feedback() {

            $feedback = array(
                'result' => true,
                'notes' => ''
            );

            $form_data = $this->sanitize_form_data($_POST['form_data']);

            foreach($form_data as $field_key => $field_val) {

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

                if( $field_key == 'Country' ) {
                    $form_data['Country'] = str_replace( '\\', '', $form_data['Country'] );
                }

                // Validate email
                if($field_key == 'Email' && ! empty($field_val) && ! filter_var($field_val ,FILTER_VALIDATE_EMAIL)) {
                    $feedback['result'] = false;

                    $feedback['notes'][] = array(
                        $field_key,
                        'Please use a valid email address.'
                    );
                }
            }

            // Validate captcha
//            $captcha_secret = get_field('vari__contact-captcha', $_POST['post_id'])['secret_key'];
//
//            $captcha_resp = json_decode(file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret=' . $captcha_secret . '&response=' . $_POST['g-recaptcha-response']), true);
//
//            if(! $captcha_resp['success']) {
//                $feedback['result'] = false;
//
//                $feedback['notes'][] = array(
//                    'captcha',
//                    'Please fill in the ReCaptcha checkbox.'
//                );
//            }

//            if($feedback['result']) {

                // Set Privacy Policy date
                $form_data['Privacy_Policy__c'] = date('Y-m-d\TH:i:s\Z');

                // Pass to SFDC
                $sfdc_resp = $this->sfdc->pass_lead_to_sfdc($form_data);

                // Log the lead in the DB
                $postid = wp_insert_post(array(
                    'post_type'     => 'form-lead',
                    'post_title'    =>  $form_data['FirstName'] . ' ' . $form_data['LastName'] . ' ' . date('d/m/Y'),
                    'post_content'  => '',
                    'post_status'   => 'publish',
                    'post_author'   => 1
                ));

                // Dispatch en email on successful lead submission
                if($sfdc_resp['success']) {
                    $item = get_post(esc_html($_POST['post_id']));
                    $item_id = $item->ID;

                    if(get_post_type($item) == 'product') {
                        $item_id = 'option';
                    }

                    $to = get_field('vari_contact-email--addresses', $item_id);
                    $subject = get_field('vari_contact-email--subject', $item_id);
                    $body = get_field('vari_contact-email--body', $item_id);

                    foreach($form_data as $key => $value) {
                        $subject = str_replace('{' . $key . '}', $value, $subject);
                        $body = str_replace('{' . $key . '}', $value, $body);
                    }

                    $mail = wp_mail($to, $subject, $body, array(
                        'Content-Type: text/html; charset=UTF-8'
                    ));

                    foreach($form_data as $key => $value) {
                        update_field('variscite__leads-' . $key, $value, $postid);
                    }

                    // Update the SFDC and email fields
                    update_field('variscite__leads-sfdc', 1, $postid);

                    if($mail) {
                        update_field('variscite__leads-email-sent', 1, $postid);
                    }
                }

                // Update the SFDC response
                update_field('variscite__leads-sfdc-resp', $sfdc_resp['message'], $postid);

                die(json_encode(array(
                    'result' => $sfdc_resp,
                    'notes' => $sfdc_resp ? 'success' : 'An error occurred. Please try again.'
                )));
//            }

            die(json_encode($feedback));
        }

        private function sanitize_form_data($data) {
            $data_array = array();

            foreach($data as $datum) {
                $data_array[esc_html($datum['key'])] = esc_html($datum['val']);
            }

            return $data_array;
        }
    }