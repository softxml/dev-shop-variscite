(function($) {      
    "use strict";      

    //Test Order Function click
    $(document).on('click','#variscite-paypal-test-order',function(){
        test_order()
    });
    
    //Test Order function
    function test_order(order_id){
        console.log("Call test");
        $.ajax({
            url: paypal_ajax.url,
            type: 'POST',
            data: {
                'action': 'variscite_paypal_ajax_test_order'                
            },
        });
    }
	
    async function create_wc_order(){
        let result;
        try{
            let order_total_amount = 0;
            let variscite_formdata = $('form.checkout').serializeArray();

            result = await $.ajax({
                url: paypal_ajax.url,
                type: 'POST',
                data: {
                    'action': 'variscite_paypal_ajax_order',
                    'checkout_data': variscite_formdata,
                },
            });
            return result;
        }catch(error){
            console.error(error);
        }
    }

    async function update_order_status( order_id, order_status, details = '' ){
        let result;
        try {
            
            result = await $.ajax({
                type:'POST',
                url: paypal_ajax.url,
                data: {
                    'action': 'variscite_update_paypal_ajax_order',
                    'order_id': order_id,
                    'order_status': order_status,
                    'details': details,
                },
            });

            return result;

        } catch (error) {
            console.error(error);
        }
    }
    
    async function custom_checkout_form_validation(){
        var result = '';
        try {            
            let variscite_formdata = $('form.checkout').serialize();

            $.ajax({
                url: paypal_ajax.url,
                type: 'POST',
                data: {
                    'action': 'variscite_paypal_ajax_form_validation',
                    'checkout_data': variscite_formdata,
                },
				success: function(response){
					result = response;
				}
            });
            return result;
        } catch (error) {
           console.error(error);
        }
    }

    function variCustomVal(){
        var varic_is_valid = true;
        var row_input;

        jQuery('.form-row').each(function(){
            // Remove all error notes from inputs
            jQuery(this).removeClass('variscite-invalid').find('.error-note').remove();

            if(jQuery(this).is(':visible')) {

                if(jQuery(this).find('input').length) {

                    // Check if checkbox is checked for terms and conditions field
                    if(jQuery(this).parents('.submit-and-terms').length) {
                        row_input = jQuery(this).find('input[type="checkbox"]');
                    } else {
                        row_input = jQuery(this).find('input');
                    }

                } else {
                    row_input = jQuery(this).find('select');
                }

                // Validate all required fields are filled
                if(jQuery(this).hasClass('validate-required') || (jQuery(this).hasClass('validate-state') && jQuery(this).find('select') && jQuery(this).find('select').length > 0)) {

                    if (
                        ((! row_input.val() || row_input.val().length <= 0) && ! row_input.is(':checkbox')) ||
                        (row_input.is(':checkbox') && ! row_input.is(':checked'))
                    ) {
                        jQuery(this).addClass('variscite-invalid').append('<span class="error-note">This field is required</span>');
                        varic_is_valid = false;
                    }
                }

                // Validate English content
                if(
                    row_input.attr('type') != 'tel' &&
                    ! validateEnglish(row_input.val()) &&
                    ! row_input.is(':checkbox') &&
                    ! jQuery(this).hasClass('variscite-invalid') &&
                    ! jQuery(this).hasClass('is-visually-hidden')
                ) {
                    jQuery(this).addClass('variscite-invalid').append('<span class="error-note">Only English input is allowed</span>');
                    varic_is_valid = false;
                }

                // Validate first and last names
                if(
                    (
                        row_input.attr('name') == 'billing_first_name' ||
                        row_input.attr('name') == 'billing_last_name' ||
                        row_input.attr('name') == 'shipping_first_name' ||
                        row_input.attr('name') == 'shipping_last_name'
                    ) &&
                    ! validateOnlyEnglish(row_input.val()) &&
                    ! jQuery(this).hasClass('variscite-invalid') &&
                    ! jQuery(this).hasClass('is-visually-hidden')
                ) {
                    if(window.location.href.indexOf(".de") > -1) {
                        var field_name = row_input.parents('.form-row').find('> label').text().replace('*', '').toLowerCase().trim();
                        jQuery(this).addClass('variscite-invalid').append('<span class="error-note">Bitte geben Sie einen gültigen ' + field_name + 'n ein</span>');
                        varic_is_valid = false;
                    } else {
                        var field_name = row_input.parents('.form-row').find('> label').text().replace('*', '').toLowerCase();
                        jQuery(this).addClass('variscite-invalid').append('<span class="error-note">Please enter a valid ' + field_name + '</span>');
                        varic_is_valid = false;
                    }
                }

                // Validate Email
                if(row_input.attr('type') == 'email' && ! validateEmail(row_input.val()) && ! jQuery(this).hasClass('variscite-invalid')) {
                    jQuery(this).addClass('variscite-invalid').append('<span class="error-note">Please enter a valid email address</span>');
                    varic_is_valid = false;
                }

                // Validate phone
                //  if(row_input.attr('type') == 'tel' && ! validatePhone(row_input.val()) && ! jQuery(this).hasClass('variscite-invalid')) {
                //      jQuery(this).addClass('variscite-invalid').append('<span class="error-note">Please enter a valid phone number</span>');
                //      checkout_is_valid = false;
                //  }

                if(row_input.attr('type') == 'tel' && ! jQuery(this).hasClass('variscite-invalid')) {
                    var input_wrapper = jQuery(this);

                    /* jQuery.ajax({
                        type: 'POST',
                        url: '/wp-admin/admin-ajax.php',
                        data: {
                            action: 'validate_phone_checkout',
                            phone: row_input.val()
                        },
                        success: function(data) {
                            if(jQuery.parseJSON(data).is_valid == false) {
                                input_wrapper.addClass('variscite-invalid').append('<span class="error-note">Please enter a valid phone number</span>');
                                varic_is_valid = false;
                            }
                        }
                    }); */
                }

                // Validate phone length
                if(row_input.attr('type') == 'tel' && row_input.val().length > 20 && ! jQuery(this).hasClass('variscite-invalid')) {
                    jQuery(this).addClass('variscite-invalid').append('<span class="error-note">The phone number must be under 20 characters.</span>');
                    varic_is_valid = false;
                }

                // Validate the phone field
                if(row_input.attr('type') == 'tel' && ! validatePhone(row_input.val()) && ! jQuery(this).hasClass('variscite-invalid')) {
                    jQuery(this).addClass('variscite-invalid').append('<span class="error-note">Please enter a valid phone number</span>');
                    varic_is_valid = false;
                }

                // Validate the company reg number field
                if( row_input.attr('name') == 'billing_company_reg_number' && ! jQuery(this).hasClass( 'variscite-invalid' ) ){
                    var country = jQuery('#billing_country').val();

                    if( country == 'IL' && row_input.val().length <= 0 ){
                        jQuery(this).addClass('variscite-invalid').append('<span class="error-note">Please enter a valid company registration number</span>');
                        varic_is_valid = false;
                    }else if( country == 'IL' && row_input.val().length != 9 ){
                        jQuery(this).addClass('variscite-invalid').append('<span class="error-note">Please enter 9 digits only</span>');
                        varic_is_valid = false;
                    }
                }

                // Validate postcode
                /* if(jQuery(this).hasClass('validate-postcode')) {
                    var country = '',
                        input_wrapper = jQuery(this);

                    if(jQuery(this).parents('.woocommerce-billing-fields').length) {
                        country = jQuery('#billing_country').val();
                    } else {
                        country = jQuery('#shipping_country').val();
                    }

                    jQuery.ajax({
                        type: 'POST',
                        url: '/wp-admin/admin-ajax.php',
                        async: false,
                        data: {
                            action: 'validate_zip_code_checkout',
                            country: country,
                            zip: row_input.val()
                        },
                        success: function(data) {

                            if(jQuery.parseJSON(data).is_valid == false) {
                                input_wrapper.addClass('variscite-invalid').append('<span class="error-note">Please enter a valid ZIP code</span>');
                                varic_is_valid = false;
                            }
                        }
                    });
                }  */

                // Max limit on both address fields
                if(
                    (row_input.attr('name') == 'billing_address_1' || row_input.attr('name') == 'billing_address_2' || row_input.attr('name') == 'shipping_address_1' || row_input.attr('name') == 'shipping_address_2') &&
                    row_input.val().length > 80
                ) {
                    jQuery(this).addClass('variscite-invalid').append('<span class="error-note">The address cannot be longer than 80 characters.</span>');
                    varic_is_valid = false;
                }

                // Max limit on the city field
                if(
                    (row_input.attr('name') == 'billing_city' || (row_input.attr('name') == 'shipping_city') && jQuery('input[name="ship_to_different_address"]').is(':checked')) &&
                    row_input.val().length > 50
                ) {
                    jQuery(this).addClass('variscite-invalid').append('<span class="error-note">The city name cannot be longer than 50 characters.</span>');
                    varic_is_valid = false;
                }
            }
        });
		
		// Validate email confirmation - billing
		var bill_email = jQuery('#billing_email'),
			bill_email_confirmation = jQuery('#billing_email_addr_confirmation');

		if(bill_email.val() !== bill_email_confirmation.val() && ! bill_email.parents('.form-row').hasClass('variscite-invalid')) {
			bill_email.parents('.form-row').addClass('variscite-invalid').append('<span class="error-note">Please confirm that the email addresses are matching.</span>');
			bill_email_confirmation.parents('.form-row').addClass('variscite-invalid');

			varic_is_valid = false;
		}
			
		// Validate email confirmation - shipping
		var shipp_email = jQuery('#shipping_email'),
			shipp_email_confirmation = jQuery('#shipping_email_addr_confirmation');

		if( jQuery('#ship-to-different-address-checkbox').is(':checked') ){
			if( shipp_email.val() !== shipp_email_confirmation.val() && ! shipp_email.parents('.form-row').hasClass('variscite-invalid') ){
				shipp_email.parents('.form-row').addClass('variscite-invalid').append('<span class="error-note">Please confirm that the email addresses are matching.</span>');
				shipp_email_confirmation.parents('.form-row').addClass('variscite-invalid');

				varic_is_valid = false;
			}
			
			if( jQuery( '#billing_country' ).val() == "IL" && jQuery('#shipping_country').val() != "IL" ){
				jQuery('#shipping_country').parents('.form-row').addClass('variscite-invalid').append( '<span class="error-note">Sorry, your request isn\'t eligible for companies in Israel. Please choose the same billing and shipping country or contact us.</span>' );
				varic_is_valid = false;
			}else if( jQuery( '#billing_country' ).val() != "IL" && jQuery('#shipping_country').val() == "IL" ){
				jQuery('#billing_country').parents('.form-row').addClass('variscite-invalid').append( '<span class="error-note">Sorry, your request isn\'t eligible for companies in Israel. Please choose the same billing and shipping country or contact us.</span>' );
				varic_is_valid = false;
			}
		}


		// Validate the company registration number field
		var billing_reg_number = jQuery('#billing_company_reg_number'),
			reg_number_regex = /(^$)|(^\d{9}$)/g;

		if(! reg_number_regex.test(billing_reg_number.val()) && ! billing_reg_number.parents('.form-row').hasClass('variscite-invalid')) {
			billing_reg_number.parents('.form-row').addClass('variscite-invalid').append('<span class="error-note">Please enter 9 digits only.</span>');
			varic_is_valid = false;
		}
		
		if( $( "tr.shipping_error" ).length ){
			var variscite_fdata = $('form.checkout').serializeArray();
            $.ajax({
                url: paypal_ajax.url,
                type: 'POST',
                data: {
                    'action': 'vari_checkout_validate_shipping_cost',
                    'checkout_data': variscite_fdata,
                },
				success: function(response){
					result = response;
				}
            });
			varic_is_valid = false;
		}
		
		if( varic_is_valid ){
			var country = '',
				input_wrapper = jQuery(this);

			if( jQuery( '.woocommerce-billing-fields' ).length ){
				country = jQuery('#billing_country').val();
			}else{
				country = jQuery('#shipping_country').val();
			}

			jQuery.ajax({
				type: 'POST',
				url: '/wp-admin/admin-ajax.php',
				async: false,
				data: {
					action: 'validate_zip_code_checkout',
					country: country,
					zip: $( "#billing_postcode" ).val()
				},
				success: function(data) {
					if( jQuery.parseJSON(data).is_valid == false ) {
						$( "#billing_postcode_field" ).addClass('variscite-invalid').append('<span class="error-note">Please enter a valid ZIP code</span>');
						varic_is_valid = false;
					}
				}
			});
			
			var variscite_formdata = $('form.checkout').serialize();
			$.ajax({
				url: paypal_ajax.url,
				type: 'POST',
				data: {
					'action': 'variscite_paypal_ajax_form_validation',
					'checkout_data': variscite_formdata,
				},
				success: function(ress){
					if( ress != "" ){
						varic_is_valid = false;
					}
				}
			});
		}
		
		if( varic_is_valid ){
			return true;
		}else{
			jQuery('html, body').animate({
				scrollTop: $($('.error-note').first()).offset().top - 200
			}, 500);
			
			return false;
		}
    }
	
	$( "body" ).on( "keyup change", "form.woocommerce-checkout .form-row", function(){
		if( $( this ).find( 'input' ).attr( 'name' ) == 'billing_email_addr_confirmation' ){
			$( "#billing_email_field" ).removeClass( 'variscite-invalid' );
			$( "#billing_email_field" ).find( 'span.error-note' ).remove();
		}
		if( $( this ).find( 'input' ).attr( 'name' ) == 'shipping_email_addr_confirmation' ){
			$( "#shipping_email_field" ).removeClass( 'variscite-invalid' );
			$( "#shipping_email_field" ).find( 'span.error-note' ).remove();
		}
		$( this ).removeClass( 'variscite-invalid' );
		$( this ).find( 'span.error-note' ).remove();
	});
	function validateEmail(value) {
		var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
		return re.test(String(value).toLowerCase());
	}

	function validateOnlyEnglish(value) {
		var re = /^[-a-zA-Z_ ]+$/;
		return re.test(String(value));
	}

	function validateEnglish(value) {
		var re = /^[\w\d\s.,&$#@()?%!\/-]*$/;
		return re.test(String(value));
	}

	function validatePhone(value) {
		var re = /[\s\#0-9_\-\+\/\(\)\.]/;
		return re.test(String(value));
	}
		
    $(document).ready(function() {                
        var top_order_id = null;
        var paypalActions;
        var resultType = false;
		
		$( "body" ).on( "change", "#billing_country", function(){
			if( $( this ).val() == "IL" ){
				$( '#billing_country' ).parents( '.form-row' ).find( '.error-note' ).remove();
			}
		});
		
		$( "body" ).on( "change", "#shipping_country", function(){
			if( $( this ).val() == "IL" ){
				$('#shipping_country').parents( '.form-row' ).find( '.error-note' ).remove();
			}
		});
		
		setTimeout(function(){
			if( $("#variscite-paypal-container").is(':visible') ){
				paypal.Buttons({
					style: {
						shape: 'rect',
						color: 'gold',
						layout: 'vertical',
						label: 'paypal',
					},     
					// Run when the user click on the paypal button
					onClick: async function(data, actions) {
						jQuery('.variscite-loader').show();
						jQuery('#variscite-paypal-container').hide();

						resultType = await variCustomVal();
						
						/*.then(
								(res) => {
									console.log( res );
									//});
										/* .then(
											(response) => {
												console.log( response );
												/* Display Checkout Form Error Message Start *
												if( response!='' ){
													jQuery('.variscite-loader').hide();
													jQuery('#variscite-paypal-container').show();

													// Remove other notices
													$( '.woocommerce-error' ).remove();
													// Display notices cart
													$( '.woocommerce-cart-form' ).before( response );
													$( 'form.checkout' ).before( response );

													$('html, body').animate({
														scrollTop: $($('.woocommerce-notices-wrapper')).offset().top - 100
													}, 500);

													resultType = false;
												}else{
													resultType = true;
												}
												/* Display Checkout Form Error Message End *
											}
										);
								});*/
								
						//var ress = custom_checkout_form_validation();
									
						/* await custom_checkout_form_validation()
							.then(
								(ress) => {
							console.log( ress );
							if( ress != '' ){
								console.log( 'first' );
								console.log( ress );
								jQuery('.variscite-loader').hide();
								jQuery('#variscite-paypal-container').show();

								// Remove other notices
								$( '.woocommerce-error' ).remove();
								// Display notices cart
								$( '.woocommerce-cart-form' ).before( ress );
								$( 'form.checkout' ).before( ress );

								$('html, body').animate({
									scrollTop: $($('.woocommerce-notices-wrapper')).offset().top - 100
								}, 500);
								
								//resultType = false;
							}else{
								console.log( 'second' );
								//resultType = true;
							}
						}); */
						jQuery('.variscite-loader').hide();
						jQuery('#variscite-paypal-container').show();
						return resultType;
					},       
					createOrder: async function(data, actions) {
						console.log("Initialize create order");
						$( '.woocommerce-error' ).remove();
						
						let paypal_order_id, paypal_order_amount = null;

						let stuff = {};
						
						//settimeout 

						// show loader
						jQuery('.variscite-loader').show();

						// hide the paypal custom container
						jQuery('#variscite-paypal-container').hide();  
						var response_status = 0;
						// Wait for the response from function call
						console.log("Initialize create_wc_order function call and wait");
						await create_wc_order()
						.then( 
							(response) => { 

								console.log(response);
								
								response_status = response.status;
								/* Hide paypal button on click for 4 sec start */
								if( response.status == true ){
									setTimeout(function () {                                
										jQuery('.variscite-loader').hide();   
										jQuery('#variscite-paypal-container').show();                                 
									}, 800);
								}

								// (@AsafA) Som Special Offer
								if (response.status == 2){                              
									jQuery('.variscite-loader').hide();
									jQuery('#variscite-paypal-container .paypal-buttons').hide();  
									jQuery('#variscite-paypal-container').show();                               
									if ( response.message ) {
										jQuery('#variscite-paypal-container').append('<span class="variscite-paypal-container-message">' + response.message + '</span>');
									}  	
								}
								/* Hide paypal button on click for 4 sec end */
								
								if( response.status == 1 ){
									stuff = response.data;
									paypal_order_id = response.data.order_id;
									paypal_order_amount = response.data.order_total;

									top_order_id = paypal_order_id;
								}else{
									jQuery('.variscite-loader').hide();
									jQuery('#variscite-paypal-container').show();
									top_order_id = 0;
								}
							}
						);
						// (@AsafA) Som Special Offer
						console.log(response_status);
						if (response_status == 2){
							return false;
						}else{
							if( top_order_id ){
								console.log('Order created and order_id is : ', paypal_order_id);
								console.log('Order created and order_amount is : ', paypal_order_amount);
								return actions.order.create({
									purchase_units: [{
										amount: {
											value: paypal_order_amount
										},
										custom_id: top_order_id
									}]
								});
							}else{
								if( $( '.entry-content' ).find( '.woocommerce-error' ).length ){
									$( '.entry-content' ).find( '.woocommerce-error' ).append( '<li>'+paypal_ajax.shipping_error+'</li>' );
								}else{
									$( '.entry-content' ).prepend( '<div class="woocommerce"><div class="woocommerce-notices-wrapper"><ul class="woocommerce-error" role="alert"><li>'+paypal_ajax.shipping_error+'</li></ul></div>' );
								}
								$('html, body').animate({
									scrollTop: $($('.woocommerce-notices-wrapper')).offset().top - 100
								}, 500);
								return "An unexpected error has occurred, Please contact us via the website";
							}
						}
						/*Set up the transaction*/
					},
					onApprove: function(data, actions) {
						// This function captures the funds from the transaction.                
						return actions.order.capture().then( async function(details) {
							// This function shows a transaction success message to your buyer.
							// console.log('Capture result', details, JSON.stringify(details, null, 2));
							// console.log('status == ', details.status, JSON.stringify(details.status));
							console.log('Transaction completed by ' + details.payer.name.given_name);
							console.log('Global order id is : ', top_order_id);

							let order_id = top_order_id;
							let order_status = 'process';

							/* hide paypal button onApprove */
							jQuery('.variscite-loader').show();
							jQuery('#variscite-paypal-container').hide();

							console.log("Initialize update_order_status");
							var currentdate = new Date();
							console.log( "process started : " + currentdate.getMinutes() + ":" + currentdate.getSeconds() );
							await update_order_status(order_id,order_status,details)
							.then( 
								(response) => {                             
									console.log("async response", response);
									console.log( "process completed : " + currentdate.getMinutes() + ":" + currentdate.getSeconds() );
									if( response.status == true && response.order_received_url!='' ){
										console.log( "redirect started : " + currentdate.getMinutes() + ":" + currentdate.getSeconds() );
										//window.location.href = response.order_received_url;
										// Simulate an HTTP redirect:
										//test_order(order_id);
										window.location.replace( response.order_received_url );

									}                                                 
								}
							);              
						});
					},
					onCancel: async function (data) {
						console.log('created order id is top_order_id', top_order_id);
						let order_id = top_order_id;
						let order_status = 'cancel';
						//update_order_status(order_id,order_status);
						//start
						await update_order_status(order_id,order_status)
						.then( 
							(response) => { 
								console.log("async response cancel", response);       
								if( response.status == true && response.order_received_url!='' ){                                                                        
									//window.location.href = response.order_received_url;
								}                                                 
							} 
						);
					},
					onError: function(err){
						//console.log( err );
					}
				}).render('#variscite-paypal-container');
			}
		}, 10000 );
    });
})(jQuery);