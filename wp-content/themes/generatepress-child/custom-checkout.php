<?php

// Update localized strings for checkout
function variscite_update_checkout_locale_strings($locale) {

    foreach($locale as $loc_key => $loc) {
        $locale[$loc_key]['postcode']['label'] = __('ZIP Code', 'woocommerce');
        $locale[$loc_key]['state']['label'] = __('State', 'woocommerce');
        $locale[$loc_key]['city']['label'] = __('City', 'woocommerce');
    }

    return $locale;
}
add_filter('woocommerce_get_country_locale', 'variscite_update_checkout_locale_strings');


// Remove the state fields for countries without states
function checkout_country() {
    $response = [
        'bill_currCountry_state' => false, 
        'shipp_currCountry_state' => false, 
        'bill_currCountry_eori' => false, 
        'shipp_currCountry_eori' => false
    ];

    $bill_currCountry = $_POST['bill_currCountry'];
    $shipp_currCountry = $_POST['shipp_currCountry'];

    // state
    $states_from_SFDC_list = get_field('woo_to_sfdc_states', 'option');
    $countries_with_states = array();

    foreach($states_from_SFDC_list as $state) {
        $countries_with_states[] = $state['woo_to_sfdc_states_cc'];
    }

    if(in_array($bill_currCountry, $countries_with_states)) {
        $response['bill_currCountry_state'] = true;
    }
    if(in_array($shipp_currCountry, $countries_with_states)) {
        $response['shipp_currCountry_state'] = true;
    }

    echo json_encode($response);
    die();
}
add_action('wp_ajax_checkout_country', 'checkout_country');
add_action('wp_ajax_nopriv_checkout_country', 'checkout_country');


function state_filed_manipulation_js() {
    if(is_checkout()) {
        ?>

        <script type="text/javascript">
            var checking_state = false;
            jQuery('body').on('update_checkout', function() {

                var billing_country_selecor = 'select#billing_country';    
                var shipping_country_selecor = 'select#shipping_country'; 
                var eori_field_countries = jQuery('#billing_eori').data('eori_countries');
                var eori_field_notice = jQuery('#billing_eori').data('eori_notice');
                var bill_currCountry = jQuery(billing_country_selecor);
                var shipp_currCountry = jQuery(shipping_country_selecor);
                
                function toggle_billing_eori_field(bill_currCountry, eori_field_countries_list){
                    if (!jQuery('#billing_eori_field').hasClass('eori_notice_added')) {
                        jQuery('#billing_eori_field').addClass('eori_notice_added');
                        jQuery('#billing_eori_field').append('<span class="eori_notice">' + eori_field_notice + '</span>')
                    }
                    if(jQuery.inArray( bill_currCountry.val(), eori_field_countries_list ) >= 0) {
                        jQuery('#billing_eori_field').show();
                    } else {
                        jQuery('#billing_eori_field').hide();
                    }
                }
                
                function toggle_shipping_eori_field(shipp_currCountry, eori_field_countries_list){
                    if (!jQuery('#shipping_eori_field').hasClass('eori_notice_added')) {
                        jQuery('#shipping_eori_field').addClass('eori_notice_added');
                        jQuery('#shipping_eori_field').append('<span class="eori_notice">' + eori_field_notice + '</span>')
                    }
                    if(jQuery.inArray( shipp_currCountry.val(), eori_field_countries_list ) >= 0) {
                        jQuery('#shipping_eori_field').show();
                    } else {
                        jQuery('#shipping_eori_field').hide();
                    }
                }

                if (eori_field_countries) {
                    var eori_field_countries_list = eori_field_countries.split(',');
                    toggle_billing_eori_field(bill_currCountry, eori_field_countries_list);
                    toggle_shipping_eori_field(shipp_currCountry, eori_field_countries_list);
                    
                    jQuery(document).on('change', billing_country_selecor, function(){
                        toggle_billing_eori_field(bill_currCountry, eori_field_countries_list);
                    });
    
                    jQuery(document).on('change', shipping_country_selecor, function(){
                        toggle_shipping_eori_field(shipp_currCountry, eori_field_countries_list);
                    });
                }
                
                  
                if (bill_currCountry.val() !== 'US' && bill_currCountry.val() !== 'CA') {
                    jQuery('#billing_state_field').hide();
                }

                if (shipp_currCountry.val() !== 'US' && shipp_currCountry.val() !== 'CA') {
                    jQuery('#shiping_state_field').hide();
                }

                jQuery.ajax({
                    type: 'POST',
                    url: '/wp-admin/admin-ajax.php',
                    dataType: "json",
                    data: {
                        action: 'checkout_country',
                        bill_currCountry: bill_currCountry.val(),
                        shipp_currCountry: shipp_currCountry.val()
                    },
                    success: function (data) {
                        // state
                        if(data.bill_currCountry_state !== true) {
                            jQuery('#billing_state_field').hide();
                        } else {
                            jQuery('#billing_state_field').show();
                        }
                        if(data.shipp_currCountry_state !== true) {
                            jQuery('#shipping_state_field').hide();
                        } else {
                            jQuery('#shipping_state_field').show();
                        }
                    }
                });
            });
        </script>

        <?php
    }
}
add_action('wp_footer', 'state_filed_manipulation_js');


// Add specific note for Israel shipping country + Non Shipping Countries
function varicsite_show_notice_shipping() {
    ?>

    <script type="text/javascript">

        jQuery('body').on('update_checkout', function() {

            // Set the country code (That will display the message)
            var countryCode = 'IL',
                bill_currCountry = jQuery('select#billing_country'),
                shipp_currCountry = jQuery('select#shipping_country');

            // Hide the company reg. number field if the country is not Israel
            if(bill_currCountry.val() === 'IL') {
                jQuery('#billing_company_reg_number_field').show();
            } else {
                jQuery('#billing_company_reg_number_field').hide();
            }

            if(countryCode === bill_currCountry.val() && ! bill_currCountry.parents('#billing_country_field').find('.il-notice').length) {
                bill_currCountry.parents('#billing_country_field').append('<p class="il-notice"><?php _e("Important notice: No delivery service in this region. Self Pickup only from Variscite\'s warehouse: 15, Izmargad st. Kiryat-Gat, Israel.");?></p>');
            } else if(countryCode !== bill_currCountry.val()) {
                bill_currCountry.parents('#billing_country_field').find('.il-notice').remove();
            }

            if(countryCode === shipp_currCountry.val() && ! shipp_currCountry.parents('#shipping_country_field').find('.il-notice').length) {
                shipp_currCountry.parents('#shipping_country_field').append('<p class="il-notice"><?php _e("Important notice: No delivery service in this region. Self Pickup only from Variscite\'s warehouse: 15, Izmargad st. Kiryat-Gat, Israel.");?></p>');
            } else if(countryCode !== shipp_currCountry.val()) {
                shipp_currCountry.parents('#shipping_country_field').find('.il-notice').remove();
            }

            // DDU field
            if((jQuery('input[name="ship_to_different_address"]').is(':checked') && countryCode === shipp_currCountry.val()) || countryCode === bill_currCountry.val()) {
                jQuery('.checkout.woocommerce-checkout').addClass('country-is-il');
            } else {
                jQuery('.checkout.woocommerce-checkout').removeClass('country-is-il');
            }

            // State field (hidden if not select)
            jQuery('.validate-state').each(function() {

                if(jQuery(this).attr('id') == 'billing_state_field') {

                    if(jQuery('#billing_country').val() == 'US' || jQuery('#billing_country').val() == 'CA') {
                        jQuery('.woocommerce-billing-fields').addClass('country-has-state');
                    } else {
                        jQuery('.woocommerce-billing-fields').removeClass('country-has-state');
                    }

                } else {

                    if(jQuery('#shipping_country').val() == 'US' || jQuery('#shipping_country').val() == 'CA') {
                        jQuery('.woocommerce-shipping-fields').addClass('country-has-state');
                    } else {
                        jQuery('.woocommerce-shipping-fields').removeClass('country-has-state');
                    }
                }
            });
            ///////*************** NON-SHIPPING + WRONG EMAIl ***************////////
            if (jQuery('form').hasClass('checkout')){

                const noDelivery = ['AM', 'BY',  'KZ', 'KG', 'MD', 'RU', ' TJ', 'TM', 'UA', 'UZ', 'CU', 'GE', 'IQ', 'IR', 'LA', 'LY', 'MO', 'MN', 'KP', 'SD', 'SY' ,'MY'];

                function privateEmailBill() {
                    const emailsList = [<?php echo the_field('variscite-shop-checkout_form_private_email', 'option'); ?>];
                    const billingEmailInput = document.getElementById('billing_email').value.toLowerCase();
                    const privateEmail_bill = emailsList.some(element => {
                        if (billingEmailInput.includes(element)) {
                            return true;
                        }
                        return false;
                    });
                    if (privateEmail_bill) {
                        return true;
                    }
                    return false;
                }
                function privateEmailShip() {
                    const emailsList = [<?php echo the_field('variscite-shop-checkout_form_private_email', 'option'); ?>];
                    const shippingEmailInput = document.getElementById('shipping_email').value.toLowerCase();
                    const privateEmail_ship = emailsList.some(element => {
                        if (shippingEmailInput.includes(element)) {
                            return true;
                        }
                        return false;
                    });
                    if (privateEmail_ship) {
                        return true;
                    }
                    return false;
                }


                jQuery('#billing_email').blur(function () {
                    if (privateEmailBill() == true) {
                        jQuery('.no-billing-email-not').remove();

                        jQuery('p#billing_phone_field').prepend("<h5 class='no-billing-email-not' style='color: #c2021b;'><?php echo the_field('variscite-shop-private_email_note', 'option'); ?></h5>");
                        // jQuery('#place_order').prop('disabled', true);
                        jQuery('#variscite-paypal-container').addClass('disabled');
                    }
                    else if (jQuery("div.shipping_address").is(":visible")) {
                        if (privateEmailBill() == false && privateEmailShip() == true){
                            // jQuery('#place_order').prop('disabled', true);
                            jQuery('#variscite-paypal-container').addClass('disabled');
                            jQuery('.no-billing-email-not').remove();
                        }
                        else if (privateEmailBill() == false && !noDelivery.includes(document.getElementById('shipping_country').value) && noDelivery.includes(document.getElementById('billing_country').value)) {
                            // jQuery('#place_order').prop('disabled', false);
                            jQuery('#variscite-paypal-container').removeClass('disabled');
                            jQuery('.no-billing-email-not').remove();
                        }
                        else if (privateEmailBill() == false){
                            // jQuery('#place_order').prop('disabled', false);
                            jQuery('#variscite-paypal-container').removeClass('disabled');
                            jQuery('.no-billing-email-not').remove();
                        }

                    }
                    else if (privateEmailBill() == false && noDelivery.includes(document.getElementById('billing_country').value)) {
                            // jQuery('#place_order').prop('disabled', true);
                            jQuery('#variscite-paypal-container').addClass('disabled');
                            jQuery('.no-billing-email-not').remove();
                    } else {
                            // jQuery('#place_order').prop('disabled', false);
                            jQuery('#variscite-paypal-container').removeClass('disabled');
                            jQuery('.no-billing-email-not').remove();
                            jQuery('.no-shipping-email-not').remove();
                        }

                })

                jQuery('#shipping_email').blur( function () {
                    if (jQuery("div.shipping_address").is(":visible")){
                        if (privateEmailShip() === true) {
                            jQuery('.no-shipping-email-not').remove();

                            jQuery('p#shipping_phone_field').prepend("<h5 class='no-shipping-email-not' style='color: #c2021b;'><?php echo the_field('variscite-shop-private_email_note', 'option'); ?></h5>");
                            // jQuery('#place_order').prop('disabled', true);
                            jQuery('#variscite-paypal-container').addClass('disabled');
                        }
                        else if (privateEmailShip() == false && privateEmailBill() == true) {
                            // jQuery('#place_order').prop('disabled', true);
                            jQuery('#variscite-paypal-container').addClass('disabled');
                            jQuery('.no-shipping-email-not').remove();
                        }
                        else if (privateEmailShip() == false  && noDelivery.includes(document.getElementById('billing_country').value) && !noDelivery.includes(document.getElementById('shipping_country').value)) {
                            // jQuery('#place_order').prop('disabled', false);
                            jQuery('#variscite-paypal-container').removeClass('disabled');
                            jQuery('.no-shipping-email-not').remove();
                        }
                        else if (privateEmailShip() == false && noDelivery.includes(document.getElementById('billing_country').value)) {
                            // jQuery('#place_order').prop('disabled', true);
                            jQuery('#variscite-paypal-container').addClass('disabled');
                            jQuery('.no-shipping-email-not').remove();
                        }
                        else if (privateEmailShip() == false && noDelivery.includes(document.getElementById('shipping_country').value)) {
                            // jQuery('#place_order').prop('disabled', true);
                            jQuery('#variscite-paypal-container').addClass('disabled');
                            jQuery('.no-shipping-email-not').remove();
                        }
                        else {
                            jQuery('.no-shipping-email-not').remove();
                            // jQuery('#place_order').prop('disabled', false);
                            jQuery('#variscite-paypal-container').removeClass('disabled');
                        }
                    }
                })


                jQuery('#billing_country').change( function () {
                    if (noDelivery.includes(document.getElementById('billing_country').value)) {
                        jQuery('.no-billing-not').remove();
                        jQuery('p#billing_country_field').append("<h5 class='no-billing-not R' style='color: #c2021b'><?php echo the_field('non_shipping_countries_error_note', 'option'); ?></h5>");
                        // jQuery('#place_order').prop('disabled', true);
                        jQuery('#variscite-paypal-container').addClass('disabled');
                    }
                    else if (privateEmailBill() == true || privateEmailShip() == true) {
                        // jQuery('#place_order').prop('disabled', true);
                        jQuery('#variscite-paypal-container').addClass('disabled');
                        jQuery('.no-billing-not').remove();
                    }
                    else {
                        // jQuery('#place_order').prop('disabled', false);
                        jQuery('#variscite-paypal-container').removeClass('disabled');
                        jQuery('.no-billing-not').remove();
                    }

                    if (jQuery("div.shipping_address").is(":visible")) {
                        if (noDelivery.includes(document.getElementById('billing_country').value) && jQuery('#shipping_country').val() == "") {
                         // jQuery('#place_order').prop('disabled', true);
                         jQuery('#variscite-paypal-container').addClass('disabled');
                        }
                        else if (noDelivery.includes(document.getElementById('billing_country').value) && !noDelivery.includes(document.getElementById('shipping_country').value) && (privateEmailBill() == false && privateEmailShip() == false)) {
                            // jQuery('#place_order').prop('disabled', false);
                            jQuery('#variscite-paypal-container').removeClass('disabled');
                        }
                        else if (!noDelivery.includes(document.getElementById('billing_country').value) && noDelivery.includes(document.getElementById('shipping_country').value)) {
                            // jQuery('#place_order').prop('disabled', true);
                            jQuery('#variscite-paypal-container').addClass('disabled');
                        }
                    }
                })

                if (jQuery("div.shipping_address").is(":visible")){
                    jQuery('#shipping_country').change( function () {
                        if (noDelivery.includes(document.getElementById('shipping_country').value)) {
                            jQuery('.no-shipping-not').remove();
                            jQuery('p#shipping_country_field').append("<h5 class='no-shipping-not' style='color: #c2021b'><?php echo the_field('non_shipping_countries_error_note', 'option'); ?></h5>");
                            // jQuery('#place_order').prop('disabled', true);
                            jQuery('#variscite-paypal-container').addClass('disabled');
                        }
                        else if (privateEmailBill() == true || privateEmailShip() == true) {
                            jQuery('.no-shipping-not').remove();
                            // jQuery('#place_order').prop('disabled', true);
                            jQuery('#variscite-paypal-container').addClass('disabled');
                        }
                        else {
                            jQuery('.no-shipping-not').remove();
                            // jQuery('#place_order').prop('disabled', false);
                            jQuery('#variscite-paypal-container').removeClass('disabled');
                        }
                        if (noDelivery.includes(document.getElementById('shipping_country').value) && !noDelivery.includes(document.getElementById('billing_country').value)) {
                            // jQuery('#place_order').prop('disabled', true);
                            jQuery('#variscite-paypal-container').addClass('disabled');
                        }
                        else if (!noDelivery.includes(document.getElementById('shipping_country').value) && noDelivery.includes(document.getElementById('billing_country').value) && (privateEmailBill() == false && privateEmailShip() == false)) {
                            // jQuery('#place_order').prop('disabled', false);
                            jQuery('#variscite-paypal-container').removeClass('disabled');
                        }
                    })
                }

            }

        });
        // clear country and email validation on shipping when shipping is unchecked //
        jQuery('#ship-to-different-address-checkbox').click(function() {
            if (!jQuery(this).is(':checked')) {
                jQuery('#shipping_country').val('').change();
                jQuery('#shipping_email').val('').change().blur();
            };
        });
    </script>

    <?php
}
add_action( 'woocommerce_after_checkout_form', 'varicsite_show_notice_shipping' );

// Hide the company reg. number field if the country is not Israel
function company_number_filed_manipulation_js() {
    if(is_checkout()) {
        ?>
        <script type="text/javascript">
            jQuery('body').on('update_checkout', function() {

                var bill_currCountry = jQuery('select#billing_country').val();

                if(bill_currCountry === 'IL') {
                    jQuery('#billing_company_reg_number_field').show();
                } else {
                    jQuery('#billing_company_reg_number_field').hide();
                }
            });
        </script>

        <?php
    }
}
//add_action('wp_footer', 'company_number_filed_manipulation_js');