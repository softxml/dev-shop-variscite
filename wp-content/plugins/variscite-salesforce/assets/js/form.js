jQuery(function($) {

    // Contact form submission action
    $('.vari-contact-form').submit(function(e) {
        e.preventDefault();

        var the_form = $(this),
            form_data = [];

        var is_valid = true;

        the_form.find('.vari-error, .vari-captcha-response').html('');
        the_form.find('.is-invalid').removeClass('is-invalid');

        the_form.find('input:not([type="submit"]), select, textarea').each(function() {

            if(
                ($(this).attr('name') === 'Privacy_Policy__c' && $(this).is(':checked')) ||
                $(this).attr('name') !== 'Privacy_Policy__c'
            ) {
                form_data.push({
                    key: $(this).attr('name'),
                    val: $(this).val()
                });
            }

            // Run the front end validation
            if((! $(this).val() || $(this).val().length <= 0) && $(this).hasClass('is-required')) {
                $(this).parents('fieldset').addClass('is-invalid');
                $(this).parents('fieldset').find('.vari-error').html('This field is required.');
                is_valid = false;
            }

            /// roi 14.06.23 #1///
			if( $( '#som-checkboxes' ).length ){
				var platformSelect = $('#som-checkboxes');
				var somPlatform = $('#som-checkboxes .multi-checkbox.checked').length;

				if( somPlatform < 1 ) {
					$(platformSelect).parents('fieldset').addClass('is-invalid');
					$(platformSelect).parents('fieldset').find('.vari-error').html('Please select SoM Platform.');
					is_valid = false;
				}
			}
			
            var quantitySelect = $('select[name="Projected_Quantities__c"]');
            var estQuant = quantitySelect.val();

            if (estQuant === '') {
                $(quantitySelect).parents('fieldset').addClass('is-invalid');
                $(quantitySelect).parents('fieldset').find('.vari-error').html('Please select Estimated Project Quantity.');
                is_valid = false;
            }
            /// roi 14.06.23 #1 - end///

            if($(this).attr('name') == 'Email' && ! validateEmail($(this).val())) {
                $(this).parents('fieldset').addClass('is-invalid');
                $(this).parents('fieldset').find('.vari-error').html('Please use a valid email address.');
                is_valid = false;
            }
        });

        if(is_valid) {

            $.ajax({
                type: 'POST',
                url: variform.ajax_url,
                data: {
                    action: 'contact_form_feedback',
                    form_data: form_data,
                    post_id: variform.post_id,
                    // captcha: grecaptcha.getResponse()
                },
                beforeSend: function() {
                    the_form.find('input[type="submit"]').prop('disabled', true).addClass('is-sending');
                },
                success: function(resp_encoded) {
                    var resp = $.parseJSON(resp_encoded);
                    the_form.find('input[type="submit"]').prop('disabled', false).removeClass('is-sending');

                    if(! resp.result) {
                        // grecaptcha.reset();

                        $.each(resp.notes, function() {

                            // if(this[0] == 'captcha') {
                            //     $('.vari-captcha-response').html(this[1]);
                            // } else {
                                $('[name="' + this[0] + '"]').parents('fieldset').find('.vari-error-notes').html(this[1]);
                            // }
                        });

                    } else {
                        window.location.href = the_form.attr('data-redirect');
                    }
                }
            });
        }
    });

    // Contact form animation
    jQuery(document).on('focus', '.vari-contact-form input, .vari-contact-form textarea',function() {
        jQuery(".vari-contact-form label[for='" + this.id + "']").addClass("labelmoveinfocus");
    });

    jQuery(document).on('blur', '.vari-contact-form input, .vari-contact-form textarea',function() {

        if( jQuery( this ).val()  == '') {
            jQuery(".vari-contact-form label[for='" + this.id + "']").removeClass("labelmoveinfocus");
        }
    });

    function validateEmail(email) {
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }
});