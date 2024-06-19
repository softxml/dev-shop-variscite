jQuery(function($) {

    // Pre-fill the country field based on the user's country
    $.ajax({
        type: 'GET',
        url: 'https://ipapi.co/json/',
        success: function(json) {
            var cc = json.country_code;
            $('.newsletter-form').find('[name="country"]').val(cc);
        }
    });

    // Newsletter submission action
    $('.newsletter-form').submit(function(e) {
       e.preventDefault();

       var the_form = $(this),
           form_data = [];

        var is_valid = true;

        the_form.find('input:not([type="submit"])').each(function() {

           if(
               $(this).attr('name') === 'privacy' && $(this).is(':checked') ||
               $(this).attr('name') !== 'privacy'
           ) {
               form_data.push({
                   key: $(this).attr('name'),
                   val: $(this).val()
               });
           }

            // Run the front end validation
            if(
                ($(this).attr('name') !== 'country' && $(this).attr('name') !== 'company-name') &&
                (! $(this).val() || $(this).val().length <= 0) && is_valid
            ) {
                the_form.find('.newsletter-response').addClass('is-invalid').find('span').html('All fields are required.');
                is_valid = false;
            }

            if($(this).attr('name') == 'email' && ! validateEmail($(this).val()) && is_valid) {
                the_form.find('.newsletter-response').addClass('is-invalid').find('span').html('Please use a valid email address.');
                is_valid = false;
            }

            if($(this).attr('name') === 'privacy' && ! $(this).is(':checked') && is_valid) {
                the_form.find('.newsletter-response').addClass('is-invalid').find('span').html('Please accept the privacy policy.');
                is_valid = false;
            }
        });

        if(is_valid) {

            $.ajax({
                type: 'POST',
                url: varinews.ajax_url,
                data: {
                    action: 'newsletter_form_feedback',
                    form_data: form_data
                },
                beforeSend: function() {
                    the_form.find('.newsletter-response').removeClass('is-invalid').removeClass('is-valid').find('span').html('');
                },
                success: function(resp_encoded) {
                    var resp = $.parseJSON(resp_encoded);

                    if(! resp.result) {
                        the_form.find('.newsletter-response').addClass('is-invalid').find('span').html(resp.notes);
                    }

                    else {
                        the_form.parents('.newsletter-form-wrapper').addClass('is-successful');
                    }
                }
            });
        }
    });

    function validateEmail(email) {
        var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }
});