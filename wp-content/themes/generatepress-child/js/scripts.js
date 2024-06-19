jQuery(document).ready(function ($){
	// Set the index value in sessionStorage
    function setIndexInSession(index) {
        sessionStorage.setItem('data_index', index);
    }
    function setCat4Session(index) {
        sessionStorage.setItem('data_cat', index);
    }
    

    // Get the index value from sessionStorage
    function getIndexFromSession() {
        return sessionStorage.getItem('data_index');
    }
       function getCat4FromSession() {
        return sessionStorage.getItem('data_cat');
    }
	//
    // // cookie notice
    // const cookieName = 'cookie_notice';
    //
    // // Check cookie
    // function checkCookie() {
    //     let date = new Date();
    //     let cookieNotice = localStorage.getItem(cookieName);
    //     let isSet = cookieNotice && ( date.getTime() < parseInt(cookieNotice) );
    //     if (!isSet) {
    //         localStorage.removeItem(cookieName);
    //         setTimeout(function() {
    //             $('.cookie-notice').show();
    //         }, 500);
    //     }
    // };
    //
    // // Cookie button click
    // $('.close-cookie-notice').on('click', function (e) {
    //     e.preventDefault();
    //     let time = new Date().getTime() + (86400000  * 30); // 30 days
    //     localStorage.setItem(cookieName, time);
    //
    //     hideCookiePopup();
    //
    //     $('[id^="menu-item-wpml"] .sub-menu li').each(function(){
    //         var urlObj  = new URL($(this).find('a').attr('href'));
    //         var url = urlObj.origin + '/setcookienotice.html';
    //         // console.log(url);
    //         $('body').append('<iframe class="setcookienoticeiframe" style="display:none;" src="' + url + '"></iframe>');
    //     });
    //
    //
    //     setTimeout(function() {
    //         $('.setcookienoticeiframe').remove();
    //     }, 10000);
    // });
    //
    // function hideCookiePopup() {
    //     $('.cookie-notice').hide();
    //     $('#site-navigation').css("bottom", "");
    //     $('body').css("padding-bottom", "");
    // }
    //
    // function popupPosition() {
    //     if(window.matchMedia('(max-width: 990px)').matches) {
    //         var popupHeight = $('.cookie-notice').outerHeight();
    //         // console.log(popupHeight);
    //         if($('.cookie-notice').is(":visible")) {
    //             $('#site-navigation').css("bottom", popupHeight + "px");
    //         } else {
    //             $('#site-navigation').css("bottom", "");
    //         }
    //     } else {
    //         var popupHeight = $('.cookie-notice').outerHeight();
    //         if($('.cookie-notice').is(":visible")) {
    //             $('body').css("padding-bottom", popupHeight + "px");
    //         } else {
    //             $('body').css("padding-bottom", "");
    //         }
    //     }
    // }
    //
    // checkCookie();
    // setTimeout(function() {
    //     popupPosition();
    // }, 500);
    //
    // $(window).on('resize', popupPosition);

    // Header cart
    jQuery.ajax({
        type: 'POST',
        url: '/wp-admin/admin-ajax.php',
        data: {
            action: 'get_cart_count_and_dropdown'
        },
        success: function(data) {
            jQuery('.wc-menu-item').html(data);
        }
    });

    // Mobile cart
    jQuery.ajax({
        type: 'POST',
        url: '/wp-admin/admin-ajax.php',
        data: {
            action: 'vari_get_cart_items_count'
        },
        success: function(resp) {

            var data = JSON.parse(resp);

            if(data.total > 0) {
                jQuery('.wc-mobile-cart-items a').append('<span class="number-of-items">' + data.total + '</span>');
            }
        }
    });

    // 13-10-22 comment out fix
    // Single product page - mobile title fix
    // if($('body').hasClass('single-product') && $(window).width() <= 990) {
    //
    //     $('.woocommerce.single-product div.product .price').css({
    //         'padding-top': ($('.woocommerce div.product .product_title').outerHeight() + 40) + 'px'
    //     })
    // }

    // Contact page - fetch countries list using AJAX
    if(jQuery('.wpcf7-form').length && jQuery('#country_name').length) {

        jQuery.ajax({
            type: 'POST',
            url: '/wp-admin/admin-ajax.php',
            data: {
                action: 'contact_form_fill_from_acf'
            },
            success: function(data) {
                jQuery('#country_name').html(data);
            }
        });
    }

    // Append date and time to footer privacy checkbox
    if(jQuery('.mc4wp-form').length) {

        var today = new Date(),
            dd = String(today.getDate()).padStart(2, '0'),
            mm = String(today.getMonth() + 1).padStart(2, '0'),
            yyyy = today.getFullYear(),
            time = today.getHours() + ":" + today.getMinutes() + ":" + today.getSeconds();

        today = yyyy + '-' + mm + '-' + dd + ' ' + time;

        jQuery('.mc4wp-form').find('input[name="PRIVACY"]').val(today);
    }

    // Append date to contact us page form
    if(jQuery('.contact_us_form').length) {

        var today = new Date(),
            dd = String(today.getDate()).padStart(2, '0'),
            mm = String(today.getMonth() + 1).padStart(2, '0'),
            yyyy = today.getFullYear(),
            time = today.getHours() + ":" + today.getMinutes();

        today = time + ' ' + dd + '/' + mm + '/' + yyyy;

        jQuery('.contact_us_form').find('input[name="privacy-policy[]"]').val(today);
    }

    jQuery('.entry-content').on('click', '.wc_grid_mode button', function(){
        var grid = jQuery( this ).attr('data-grid');

        jQuery('.wc_grid_mode button').removeClass('active');
        jQuery( this ).addClass('active');

        if(grid == 'grid'){
            $("#content").addClass("product_grid");
        }else{
            $("#content").removeClass("product_grid");
        }

    });

    // On cart - push dataLayer events
    $('.woocommerce-cart').find('.continue_shopping a').click(function() {
        dataLayer.push({'event': 'CartPage-Click-ContinueShopping'});
    });

    $('.woocommerce-cart').find('.product-quantity button').click(function() {
       if($(this).hasClass('plus')) {
           dataLayer.push({'event': 'CartPage-Click-IncreaseAmount'});
       } else if($(this).hasClass('minus')) {
           dataLayer.push({'event': 'CartPage-Click-DecreaseAmount'});
       }
    });

    // $('.woocommerce-cart').find('.wc-proceed-to-checkout .checkout-button').click(function() {
    //     dataLayer.push({'event': 'CartPage-Click-ProceedToCheckout'});
    // });

    $('.woocommerce-cart').find('.product-remove a').click(function() {
        dataLayer.push({'event': 'CartPage-Click-RemoveItem'});
    });

    $( '.woocommerce-cart' ).on('click', '.quantity>.plus, .quantity>.minus', function() {
        // Get values
        var $qty = $( this ).closest( '.quantity' ).find( '.qty' ),
            currentVal = parseFloat( $qty.val() ),
            max = parseFloat( $qty.attr( 'max' ) ),
            min = parseFloat( $qty.attr( 'min' ) ),
            step = $qty.attr( 'step' );

        // Format values
        if ( !currentVal || currentVal === '' || currentVal === 'NaN' ) currentVal = 0;
        if ( max === '' || max === 'NaN' ) max = '';
        if ( min === '' || min === 'NaN' ) min = 0;
        if ( step === 'any' || step === '' || step === undefined || parseFloat( step ) === 'NaN' ) step = 1;

        // Change the value
        if ( $( this ).is( '.plus' ) ) {
            if ( max && ( max == currentVal || currentVal > max ) ) {
                $qty.val( max );
                // $qty.parents('.product-quantity').find('.max_qty_product').show();
            } else {
                $qty.val( currentVal + parseFloat( step ) );
            }

        } else {
            // $qty.parents('.product-quantity').find('.max_qty_product').hide();

            if ( min && ( min == currentVal || currentVal < min ) ) {
                $qty.val( min );
            } else if ( currentVal > 0 ) {
                $qty.val( currentVal - parseFloat( step ) );

            }
			
			var td = $(this).parent().parent().prev();
            console.log($(this).next().val());
            var sku = td.attr('data-sku');
            console.log('sku',sku);
            var variant = '';
            if(sku.indexOf('STK') != -1){
                variant = 'Starter kit';
            }
            if(sku.indexOf('DVK') != -1){
                variant = 'Development kit';
            }
            var curr = td.attr('data-curr');
            var price = td.attr('data-price');
            var type = td.attr('data-type');
            var index = td.attr('data-index');
            var cat4 = td.attr('data-cat');
            var system = '';
            var som = '';
            var kit = '';
            var name = td.find('a').text();
            var item_type = '';
            som = td.attr('data-pa_som');
            if(type == 'kit'){
                item_type = 'Evaluation Kits';
                system = td.attr('data-pa_system');
                kit = td.attr('data-pa_kit');
            }else if(type == 'accessory'){
                item_type = "accessory";
            }else{
                item_type = "System on Module";
            }

            dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
            dataLayer.push({
                "event": "remove_from_cart",
                "ecommerce": {
                    "currency": curr,
                    "items": [{
                        "item_id": sku,
                        "item_name": name,
                        "price": price,
                        "item_type": item_type,
                        "item_variant": variant,
                        "item_category": som,
                        "item_category2": system,
                        "item_category4": cat4,
                        "item_category5": "",
                        "quantity": 1,
                        "index": index
                    }]
                }
            });
        }

        // Trigger change event
        $qty.trigger( 'change' );
        // jQuery("[name='update_cart']").trigger("click");
    });

    jQuery('div.woocommerce').on('click', '.update_cart_trigger', function(){
        jQuery("[name='update_cart']").trigger("click");
    });

    $('#dismiss, .overlay').on('click', function () {
        $('#left-sidebar').removeClass('active');
        $('.overlay').removeClass('active');
    });

    $('.mobile_filter_icon').on('click', function () {
        $('#left-sidebar').addClass('active');
        $('.overlay').addClass('active');

    });

    var variation_top_offset;
    // Product variations
    $(document).on('change', '.variation-radios input', function() {
        $('select[name="'+$(this).attr('name')+'"]').val($(this).val()).trigger('change');

        $(this).parents('.value').siblings('.label').addClass('visited-item');

        if(jQuery(this).parents('.variation-radios.pa_kit').length) {

            var product_ids = $(this).attr('data-accessories');

            if(product_ids !== '') {

                if(
                    $('label[for="pa_operating-system"]').parents('.label').hasClass('visited') ||
                    $('label[for="pa_operating-system"]').parents('.label').hasClass('current')
                ) {
                    $('.product_cat-evaluation-kit .evaluation_kit_next_step.pa_operating-system').show();
                    $('.single-product .product_cat-evaluation-kit form.variations_form .product_accessories').addClass('visited');
                }

                $('.entry-content > .product').addClass('kit-has-accessories');
                change_product_accessories_view(product_ids);
            } else {
                $('.entry-content > .product').removeClass('kit-has-accessories');
                $('.product_cat-evaluation-kit .product_accessories').html('');
                $('.product_cat-evaluation-kit .evaluation_kit_next_step.pa_operating-system').hide();
            }


            var option = $('#pa_kit').val();

            if( option !== '') {
                $('.single-product .product_cat-evaluation-kit form.variations_form .variations tr .label').removeClass('current');
                jQuery('.product_cat-evaluation-kit .evaluation_kit_next_step.pa_kit').parents('tr').find('.label').addClass('visited');

                $('.single-product .product_cat-evaluation-kit form.variations_form .product_accessories .accessories_header_wrap').removeClass('current');

                $('.single-product .product_cat-evaluation-kit form.variations_form .variations tr:nth-child(2) .label').addClass('current');

                // Push dataLayer event
                dataLayer.push({'event': 'KitPageFunnel-1-ClickNext'});
            }
        } else if(jQuery(this).parents('.variation-radios.pa_som-configuration').length) {
            var option = $('#pa_som-configuration').val();
            if( option !== ''){

                $('.single-product .product_cat-evaluation-kit form.variations_form .variations tr .label').removeClass('current');
                $('.single-product .product_cat-evaluation-kit form.variations_form .product_accessories .accessories_header_wrap').removeClass('current');

                jQuery('.product_cat-evaluation-kit .evaluation_kit_next_step.pa_som-configuration').parents('tr').find('.label').addClass('visited');

                $('.single-product .product_cat-evaluation-kit form.variations_form .variations tr:nth-child(3) .label').addClass('current');

                dataLayer.push({'event': 'KitPageFunnel-2-ClickNext'});
            }
        } else if(jQuery(this).parents('.variation-radios.pa_operating-system').length) {

            var option = $('#pa_operating-system').val();

            if( option !== ''){
                $('.single-product .product_cat-evaluation-kit form.variations_form .variations tr .label').removeClass('current');
                $('.single-product .product_cat-evaluation-kit form.variations_form .product_accessories .accessories_header_wrap').addClass('current');

                jQuery('.product_cat-evaluation-kit .evaluation_kit_next_step.pa_operating-system').parents('tr').find('.label').addClass('visited');
                jQuery('.accessories_header_wrap').addClass('visited');

                variation_top_offset = $(".single-product .product_cat-evaluation-kit form.variations_form .product_accessories .accessories_header_wrap").offset().top;

                if(jQuery(window).width() > 990) {
                    variation_top_offset -= $('#site-navigation').outerHeight();

                    if (jQuery('body').hasClass('admin-bar')) {
                        variation_top_offset -= jQuery('#wpadminbar').outerHeight();
                    }
                }

                $('body, html').animate({
                    scrollTop: variation_top_offset - 24
                }, 1000);

                $('.entry-content > .product').addClass('kit-accessories-view');
                $('.entry-content > .product').find('.single_add_to_cart_button').removeClass('wc-variation-selection-needed');

                dataLayer.push({'event': 'KitPageFunnel-3-ClickNext'});
            }

        }

		if( $( this ).parents( 'tr' ).next( 'tr' ).length ){
			variation_top_offset = $(this).parents('tr').next('tr').offset().top;

			if(jQuery(window).width() > 990) {
				variation_top_offset -= $('#site-navigation').outerHeight();

				if (jQuery('body').hasClass('admin-bar')) {
					variation_top_offset -= jQuery('#wpadminbar').outerHeight();
				}
			}

			$('body, html').animate({
				scrollTop: variation_top_offset - 24
			}, 1000);
		}

        if(jQuery(this).is(':checked')) {
            console.log('checked');
            jQuery(this).parents('.value').find('.next_button').show();
        }
    });

    // Product steps
    if($('.single-product .product_cat-evaluation-kit .product_accessories').hasClass('visited')) {
        var accessories = $('.product_cat-evaluation-kit .variations .variation-radios.pa_kit input[type="radio"]:checked').attr('data-accessories');

        if(accessories !== ''){

            if($('label[for="pa_operating-system"]').parents('.label').hasClass('visited') || $('label[for="pa_operating-system"]').parents('.label').hasClass('current')) {
                $('.product_cat-evaluation-kit .evaluation_kit_next_step.pa_operating-system').show();
                $('.single-product .product_cat-evaluation-kit form.variations_form .product_accessories').addClass('visited');
            }

            change_product_accessories_view(accessories);
        } else {
            $('.product_cat-evaluation-kit .product_accessories').html('');
            $('.product_cat-evaluation-kit .evaluation_kit_next_step.pa_operating-system').hide();
        }
    }

    var variation_top_offset;

    jQuery('.product_cat-evaluation-kit .evaluation_kit_next_step.pa_kit .next_button,' +
        '.single-product .product_cat-evaluation-kit form.variations_form .variations tr:nth-child(2) .label').click( function(){

        var option = $('#pa_kit').val();

        if( option !== '') {
            $('.single-product .product_cat-evaluation-kit form.variations_form .variations tr .label').removeClass('current');
            jQuery('.product_cat-evaluation-kit .evaluation_kit_next_step.pa_kit').parents('tr').find('.label').addClass('visited');

            $('.single-product .product_cat-evaluation-kit form.variations_form .product_accessories .accessories_header_wrap').removeClass('current');

            $('.single-product .product_cat-evaluation-kit form.variations_form .variations tr:nth-child(2) .label').addClass('current');

            variation_top_offset = $(".single-product .product_cat-evaluation-kit form.variations_form .variations tr:nth-child(2)").offset().top;

            if(jQuery(window).width() > 990) {
                variation_top_offset -= $('#site-navigation').outerHeight();

                if(jQuery('body').hasClass('admin-bar')) {
                    variation_top_offset -= jQuery('#wpadminbar').outerHeight();
                }
            }

            $('html, body').animate({
                scrollTop: variation_top_offset - 24
            }, 1000);

            // Push dataLayer event
            dataLayer.push({'event': 'KitPageFunnel-1-ClickNext'});
        }
    });

    jQuery('.product_cat-evaluation-kit .evaluation_kit_next_step.pa_som-configuration .next_button,' +
        '.single-product .product_cat-evaluation-kit form.variations_form .variations tr:nth-child(3) .label').click( function(){
        var option = $('#pa_som-configuration').val();
        if( option !== ''){

            $('.single-product .product_cat-evaluation-kit form.variations_form .variations tr .label').removeClass('current');
            $('.single-product .product_cat-evaluation-kit form.variations_form .product_accessories .accessories_header_wrap').removeClass('current');

            jQuery('.product_cat-evaluation-kit .evaluation_kit_next_step.pa_som-configuration').parents('tr').find('.label').addClass('visited');

            $('.single-product .product_cat-evaluation-kit form.variations_form .variations tr:nth-child(3) .label').addClass('current');

            variation_top_offset = $(".single-product .product_cat-evaluation-kit form.variations_form .variations tr:nth-child(3)").offset().top;

            if(jQuery(window).width() > 990) {
                variation_top_offset -= $('#site-navigation').outerHeight();

                if (jQuery('body').hasClass('admin-bar')) {
                    variation_top_offset -= jQuery('#wpadminbar').outerHeight();
                }
            }

            $('body, html').animate({
                scrollTop: variation_top_offset - 24
            }, 1000);

            dataLayer.push({'event': 'KitPageFunnel-2-ClickNext'});
        }
    });

    // jQuery('.product_cat-evaluation-kit .evaluation_kit_next_step.pa_operating-system .next_button').click( function(){
    //     var option = $('#pa_operating-system').val();
    //
    //     if( option !== ''){
    //         $('.single-product .product_cat-evaluation-kit form.variations_form .variations tr .label').removeClass('current');
    //         $('.single-product .product_cat-evaluation-kit form.variations_form .product_accessories .accessories_header_wrap').addClass('current');
    //
    //         jQuery('.product_cat-evaluation-kit .evaluation_kit_next_step.pa_operating-system').parents('tr').find('.label').addClass('visited');
    //         jQuery('.accessories_header_wrap').addClass('visited');
    //
    //         variation_top_offset = $(".single-product .product_cat-evaluation-kit form.variations_form .product_accessories .accessories_header_wrap").offset().top;
    //
    //         if(jQuery(window).width() > 990) {
    //             variation_top_offset -= $('#site-navigation').outerHeight();
    //
    //             if (jQuery('body').hasClass('admin-bar')) {
    //                 variation_top_offset -= jQuery('#wpadminbar').outerHeight();
    //             }
    //         }
    //
    //         $('body, html').animate({
    //             scrollTop: variation_top_offset - 24
    //         }, 1000);
    //
    //         $('.entry-content > .product').addClass('kit-accessories-view');
    //         $('.entry-content > .product').find('.single_add_to_cart_button').removeClass('wc-variation-selection-needed');
    //
    //         dataLayer.push({'event': 'KitPageFunnel-3-ClickNext'});
    //     }
    // });

    jQuery('.single-product .product_cat-evaluation-kit form.variations_form .variations tr:first-child ').on('click', '.label', function(){

        $('.single-product .product_cat-evaluation-kit form.variations_form .variations tr .label').removeClass('current');
        $('.single-product .product_cat-evaluation-kit form.variations_form .product_accessories .accessories_header_wrap').removeClass('current');
        $(this).addClass('current');

        variation_top_offset = $(this).offset().top;

        if(jQuery(window).width() > 990) {
            variation_top_offset -= $('#site-navigation').outerHeight();

            if (jQuery('body').hasClass('admin-bar')) {
                variation_top_offset -= jQuery('#wpadminbar').outerHeight();
            }
        }

        $('body, html').animate({
            scrollTop: variation_top_offset - 24
        }, 1000);
    });

    jQuery('.single-product .product_cat-evaluation-kit form.variations_form .product_accessories').on('change', '.accessory-attributes input[type="radio"]', function() {

        var accessory_checkbox = jQuery(this).parents('.accessories_product_item').find('.custom_product_accessory'),
            product_id = accessory_checkbox.attr('data-product-id');

        accessory_checkbox.val(product_id + ',' + jQuery(this).val() + ',' + jQuery(this).attr('name'));
    });

    jQuery('.single-product .product_cat-evaluation-kit form.variations_form .product_accessories ').on('click', '.accessories_header_wrap', function(){
        var option = $('#pa_operating-system').val();
        if( option !== ''){
            $('.single-product .product_cat-evaluation-kit form.variations_form .variations tr .label').removeClass('current');
            $( this ).addClass('current');

            variation_top_offset = $(this).offset().top;

            if(jQuery(window).width() > 990) {
                variation_top_offset -= $('#site-navigation').outerHeight();

                if (jQuery('body').hasClass('admin-bar')) {
                    variation_top_offset -= jQuery('#wpadminbar').outerHeight();
                }
            }

            $('body, html').animate({
                scrollTop: variation_top_offset - 24
            }, 1000);
        }
    });

    // Push event upon Kit add to cart click
    $('.product_cat-evaluation-kit .single_add_to_cart_button').click(function() {
        dataLayer.push({'event': 'KitPageFunnel-4-AddToCart'});
    });

    $('.single_add_to_cart_button').click(function() {
        display_loader($(this));
    });

    // Push event when selecting kit options
    $('.product_cat-evaluation-kit').find('.variation-radios.pa_kit').find('input[type="radio"]').change(function() {
        dataLayer.push({'event': 'KitPageFunnel-Click-ChooseKit'});
    });

    $('.product_cat-evaluation-kit').find('.variation-radios.pa_som-configuration').find('input[type="radio"]').change(function() {
        dataLayer.push({'event': 'KitPageFunnel-Click-ChooseSom'});
    });

    $('.product_cat-evaluation-kit').find('.variation-radios.pa_operating-system').find('input[type="radio"]').change(function() {
        dataLayer.push({'event': 'KitPageFunnel-Click-OperatingSystem'});
    });

    $('.product_cat-evaluation-kit').find('.product_accessories').on('change', 'input[type="checkbox"]', function() {
        dataLayer.push({'event': 'KitPageFunnel-Click-ChooseAccessory'});
    });

    var accessories = $('.product_cat-evaluation-kit .variations .variation-radios.pa_kit input[type="radio"]:checked').attr('data-accessories');

    if(accessories !== ''){

        if($('label[for="pa_operating-system"]').parents('.label').hasClass('visited') || $('label[for="pa_operating-system"]').parents('.label').hasClass('current')) {
            $('.product_cat-evaluation-kit .evaluation_kit_next_step.pa_operating-system').show();
            $('.single-product .product_cat-evaluation-kit form.variations_form .product_accessories').addClass('visited');
        }

        change_product_accessories_view(accessories);
    } else {
        $('.product_cat-evaluation-kit .product_accessories').html('');
        $('.product_cat-evaluation-kit .evaluation_kit_next_step.pa_operating-system').hide();
    }

    function change_product_accessories_view(attr){

        var data = {
            product_accessories: attr,
            action: 'product_accessories'
        };

        jQuery.post('/wp-admin/admin-ajax.php', data,function(result){
            jQuery('.product_accessories').html( result.products );
        });
    }

    // Product Kit slick slider
    $('.product_cat-evaluation-kit .kit_gallery').slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        arrows: true,
        responsive: [
            {
                breakpoint: 9000,
                settings: "unslick"
            },
            {
                breakpoint: 1024,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }
        ]
    });

    // Accessories add to cart
    // jQuery('.product_cat-evaluation-kit').on('click', '.single_add_to_cart_button', function(){
    //     var product_ids = [];
    //     $('.custom_product_accessory:checked').each(function() {
    //         product_ids.push($(this).val());
    //     });
    //     let data={
    //         product_ids: product_ids,
    //         action: 'accessories_addtocart'
    //     };
    //     jQuery.post('/wp-admin/admin-ajax.php', data,function(result){
    //         console.log(result);
    //     });
    // })

    // Kit product - update price according to accessories selected (only on front-end): TODO
    if($('.product').hasClass('product_cat-evaluation-kit')) {

        // On final variation selection - set the price as an attribute to the product
        jQuery('.single_variation_wrap').on('show_variation', function(event, variation) {

            var price_wrapper = $('.single_variation_wrap').find('.woocommerce-Price-amount'),
                currency_wrapper = price_wrapper.find('.woocommerce-Price-currencySymbol'),
                currency = currency_wrapper.text(),
                original_price = parseFloat(price_wrapper.text().replace(currency, ''));

            jQuery('.variations_form').attr('data-naked-price', original_price);
        });

        // On product accessory change - recalculate the pricing with the accessory update
        jQuery('.product_accessories').on('change', 'input[type="checkbox"]', function() {

            var original_price = parseFloat(jQuery('.variations_form').attr('data-naked-price')),
                accessories_price = 0,

                price_wrapper,
                product_price_wrapper = $('.single_variation_wrap').find('.woocommerce-Price-amount'),
                currency_wrapper = product_price_wrapper.find('.woocommerce-Price-currencySymbol'),
                currency = currency_wrapper.text(),
                variation_price;

            jQuery('.product_accessories').find('.accessories_product_item').each(function() {

                if(jQuery(this).find('input[type="checkbox"]').is(':checked')) {

                    price_wrapper = jQuery(this).find('.amount');
                    variation_price = parseFloat(price_wrapper.text().replace(currency, ''));
                    accessories_price += variation_price;
                }
            });

            $('.single_variation_wrap').find('.woocommerce-Price-amount').html('<span class="woocommerce-Price-currencySymbol">' + currency + '</span>' + String(parseFloat(Math.round((accessories_price + original_price) * 100) / 100).toFixed(2)));
        });


        // $('.variations').find('.variation_item').find('input[type="radio"]').change(function() {
        //     original_price = parseInt(price_wrapper.text().replace(currency, ''));
        //     price_wrapper.attr('data-naked-price', original_price);
        // });

        // var accessories_price = 0,
        //     accessory_price = 0;
        //
        // jQuery('.product_accessories').on('change', 'input[type="checkbox"]', function() {
        //     original_price = parseInt(price_wrapper.text().replace(currency, ''));
        //     accessories_price = 0;
        //
        //     update_total_price_for_kit();
        // });
        //
        // function update_total_price_for_kit() {
        //
        //     accessories_price = 0;
        //
        //     $('.product_accessories').find('.accessories_product_item').each(function() {
        //         accessory_price = parseInt($(this).find('.woocommerce-Price-amount').text().replace(currency, ''));
        //
        //         if($(this).find('input[type="checkbox"]').is(':checked')) {
        //             accessories_price += accessory_price;
        //         }
        //     });
        //
        //     price_wrapper.html(currency_wrapper.html() + String(original_price + accessories_price));
        // }
    }

    // Simple product - push dataLayer
    $('.product-type-simple').find('form.cart').submit(function() {
        dataLayer.push({'event': 'AccessoryPage-Click-AddToCart'});
    });

    /* var proceeded = false;

    function proceedToCheckoutModified() {
        jQuery('.woocommerce-cart').find('.checkout-button').on('click', function(e) {
            e.preventDefault();
            var proceedBtn = $(this),
                pageLang = document.getElementsByTagName("html")[0].getAttribute("lang");

            if(proceeded === true) {
                dataLayer.push({'event': 'CartPage-Click-ProceedToCheckout'});
                
                window.location.href = '/checkout';
                // if(pageLang == 'de-DE') {
                //     window.location.href = '/kasse';
                // } else {
                //     window.location.href = '/checkout';
                // }

            } else {
                $('.woocommerce-cart .update_cart_trigger').click();

                if(pageLang == 'de-DE') {
                    proceedBtn.parent().prepend('<div class="checkout-notification">Bitte beachten Sie, dass Bestellungen nach der Zahlung nicht mehr geändert werden können</div>');
                    proceedBtn.text('Weitermachen');
                } else {
                    proceedBtn.parent().prepend('<div class="checkout-notification">Please notice, orders can\'t be modified after payment</div>');
                    proceedBtn.text('Continue');
                }
                proceeded = true;
            }
        });
    }

    jQuery(document.body).on( 'updated_cart_totals', function() {
        proceeded = false;
        proceedToCheckoutModified();
    });

    // Cart - update on checkout button press
    proceedToCheckoutModified();*/

    //Gallery image Popup
    jQuery('.product_cat-evaluation-kit .kit_gallery').on('click', '.image_container', function(){
        var src = $( this ).find('.gray_background').attr('data-image-src');
        var html = '<img src="' + src + '">';

        $('.kit_modal_popup').find('.modal-body').html(html);
        $('.kit_modal_popup').modal('show');
    });

    // Checkout - push dataLayer events
    jQuery('.woocommerce-checkout').find('#estimated_product_quantities input[type="radio"]').change(function() {
        dataLayer.push({'event': 'CheckoutPage-Click-EstimatedProductionQuantities'});
    });

    jQuery('.woocommerce-checkout').find('form.checkout').submit(function() {
        dataLayer.push({'event': 'CheckoutPage-Click-ProceedToPayment'});
    });

    // Checkout page coupon code alternative field
    jQuery('#woocommerce_promo_code').submit(function(e) {
        e.preventDefault();
        var code = jQuery(this).val();

        if(code && code.length > 0) {
            apply_the_coupon(code);
        }
    });

    jQuery('#woocommerce_promo_code').find('button').click(function(e) {
        e.preventDefault();
        var code = jQuery('#promo_code').val();

        if(code && code.length > 0) {
            apply_the_coupon(code);
        }
    });

    function apply_the_coupon(code) {

        var data = {
            action: 'apply_cart_coupon_code',
            code: code
        };

        jQuery('#woocommerce_promo_code').find('.woocommerce-input-wrapper').find('span.error').remove();
        jQuery('#woocommerce_promo_code').find('.form-row.promo-code').removeClass('woocommerce-invalid');

        jQuery.post('/wp-admin/admin-ajax.php', data, function(data) {

            var resp = jQuery.parseJSON(data);

            if(resp.is_valid) {
                jQuery( 'body' ).trigger( 'update_checkout' );
            } else {
                jQuery('#woocommerce_promo_code').find('.form-row.promo-code').removeClass('woocommerce-validated').addClass('woocommerce-invalid');

                jQuery('#woocommerce_promo_code').find('.woocommerce-input-wrapper').append('<span class="error">' + resp.error + '</span>');
            }

        });
    }

    // Checkout sticky right table
    if(jQuery('#order_review').length) {
        checkoutStickyTable();

        jQuery(window).resize(function() {
            checkoutStickyTable();
        });
    }

    function checkoutStickyTable() {

        if(jQuery(window).width() > 1600) {

            if(! jQuery('#order_review').hasClass('is_stuck')) {

                jQuery('#order_review').stick_in_parent({
                    offset_top: jQuery('#site-navigation').outerHeight()
                });
            }
        } else {

            if(jQuery('#order_review').hasClass('is_stuck')) {
                jQuery('#order_review').trigger('sticky_kit:detach');
            }
        }
    }

    // Contact form animation
    jQuery(document).on('focus', '.contact_us_page .vari-contact-form input, .contact_us_page .vari-contact-form textarea',function() {
        jQuery(".contact_us_form label[for='" + this.id + "']").addClass("labelmoveinfocus");
    });
    jQuery(document).on('blur', '.contact_us_page .vari-contact-form input, .contact_us_page .vari-contact-form textarea',function() {
        if( jQuery( this ).val()  == ''){
            jQuery(".contact_us_form label[for='" + this.id + "']").removeClass("labelmoveinfocus");
        }
    });

    // Redirect from contact form to thank you page on successful submission
    jQuery('.wpcf7').on('wpcf7mailsent', function(e) {
        var formid = e.detail.contactFormId;

        if(formid == 9032) {
            dataLayer.push({'event': 'form-shop-contactUs-success'});
            window.location.href = '/thank-you-for-contacting-us/';
        }
    });

    // Auto scroll to the contact form on mobile
    jQuery(document).ready(function() {

        if(jQuery('body').hasClass('page-id-441') && jQuery(window).width() < 990) {
            jQuery('html, body').animate({ scrollTop: jQuery('.contact_us_form').offset().top }, 500);
        }
        
    });

    $('.is-sending').removeClass('is-sending');
    // For Safari compatability
    $(window).bind("pageshow", function(event) {
        if (event.originalEvent.persisted) {
            $('.is-sending').removeClass('is-sending');
        }
    });

    jQuery(function($){
        $('.bottom-language-switcher').on('click', function(){
            $(this).toggleClass('open');
        });


        if ($('li.btn-filter').siblings().size() > 0) {
            $('li.btn-filter').addClass('col-md-6');
        } else {
            $('li.btn-filter').removeClass('col-md-6');
        }

    });

    jQuery('.single_add_to_cart_button').removeClass('is-sending');
	
    jQuery('.single_add_to_cart_button').on( 'click', function() {
		var index = getIndexFromSession();
		var form    = jQuery(this).parents('form'),
			var_id  = form.find( '[name="variation_id"]' ).val();

		window.dataLayer = window.dataLayer || [];

		if( product_config.vars && Object.keys(product_config.vars).length == 0 && ( !$( '.variation-radios' ).hasClass( 'pa_kit' ) ) ){
			dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
			dataLayer.push({
				"event": "add_to_cart",
				"ecommerce": {
					"currency": $('#som_curr').val(),
					"items": [{
						"item_id": $('#som_sku').val(),
						"item_name":  product_config.name,
						"price":  product_config.price,
						"item_type": product_config.type,
						"item_variant": "",
						"item_category": $('.variation_item input').val(),
						"item_category2": "",
						"item_category4": "",
						"item_category5": "",
						"quantity": 1,
						"index": index
					}]
				}
			});
			console.log(dataLayer);
			
			form.submit();
		}else{
			if( !$( this ).hasClass( "disabled" ) && !$( this ).hasClass( "wc-variation-selection-needed" ) ){
				var $_this = $( this ).parent().parent().parent();
				var index = getIndexFromSession();
				var accessories = 'no'; 
				var accessories_name = '';
				if($('.custom_product_accessory').is(":checked")){
					accessories = 'yes';
					accessories_name = $('.custom_product_accessory').attr('data-name');
				}
				var product_id = $_this.attr('data-product_id');
				var pa_kit = $('#pa_kit').find(":selected").val();
				var pa_som_configuration = $('#pa_som-configuration').find(":selected").val();
				var pa_operating_system = $('#pa_operating-system').find(":selected").val();
				//

				if( $( '.variation-radios' ).hasClass( 'pa_kit' ) ){
					console.log('cart');
					jQuery.ajax({
						type: "post",
						dataType: "json",
						url: '/wp-admin/admin-ajax.php',
						data: {
							action: "get_var_id",
							product_id: product_id,
							pa_kit: pa_kit,
							pa_som_configuration: pa_som_configuration,
							accessories: accessories,
							pa_operating_system: pa_operating_system
						},
						success: function (response) {
							if( response.data.errors ){
								alert( "הייתה בעיה בשליחת הסיסמה" );
							}
							if( response.success ){
								console.log( response.data.name );
								dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
								dataLayer.push({
									"event": "add_to_cart",
									"ecommerce": {
										"currency": response.data.curr,
										"items": [{
											"item_id": response.data.sku,
											"item_name": response.data.name,
											"price": response.data.price,
											"item_type": response.data.type,
											"item_variant": response.data.variant,
											"item_category": pa_som_configuration,
											"item_category2": pa_operating_system,
											"item_category4": accessories,
											"item_category5": '' ,
											"quantity": 1,
											"index": index
										}]
									}
								});
								console.log('dataLayer' , dataLayer);
								setIndexInSession( index );
								setTimeout( function(){
									//$_this.submit();
								}, 500 );
							}

						}
					})
				}
			}
		}
		
        setTimeout(function() {
            jQuery('.is-sending').removeClass('is-sending');
        }, 3000);
    });
	
	$( "body" ).on( "keyup", ".woocommerce-checkout input.input-text,.woocommerce-checkout input.input-radio", function(){
		var input_name = $( this ).attr( 'name' );
		var input_val = $( this ).val();
		document.cookie = input_name+"="+input_val+"; expires=0; path=/";
	});
	$( "body" ).on( "change", ".woocommerce-checkout select", function(){
		var input_name = $( this ).attr( 'name' );
		var input_val = $( this ).val();
		document.cookie = input_name+"="+input_val+"; expires=0; path=/";
	});
});

function display_loader(element) {
    element.addClass('is-sending');
}

(function($){
    $(document).ready(function() {
        $('#primary-menu .menu-item').on('click', function() {
            console.log($(this).text());
            //Push data to the data layer of menu-items
            dataLayer.push({
                'event': 'menu_shop',
                'label_event':$(this).text()
            });
			console.log( JSON.stringify( dataLayer ) );
        });

    $('.inside-left-sidebar .menu-item').on('click', function() {
        console.log($(this).text());
        // push data of products filter
        dataLayer.push({
            'event':'filter_cpu',
            'label_event':$(this).text()
        });
    });

        if($('body').hasClass('post-type-archive-product')){
            console.log('archive');
            var itemsArray = [];
            // Clear the previous ecommerce object
            dataLayer.push({ ecommerce: null });
            // Create the new data layer object
            // var itemList = {
            //     event: "view_item_list",
            //     ecommerce: {
            //         items: []
            //     }
            // };
            var i =1;
            // Loop through the items array and push each item to the itemList
            $('.archive .products .add_to_cart_button').each(function(){
                var name = $(this).attr("data-name");
                var price = $(this).attr("data-price");
                var type = $(this).attr("data-type");
                $(this).attr('data-index', i);
                itemsArray.push({
                    item_name: name,
                    price: price,
                    item_type: type,
                    index: i
                });
                // itemList.ecommerce.items.push(dynamicItem);
                i++;
            });
            // Push the new data layer object to the dataLayer array
            dataLayer.push({
                    event: "view_item_list",
                    ecommerce: {
                        items: itemsArray
                    }
                });
			console.log( JSON.stringify( dataLayer ) );
        }

        $('.archive .add_to_cart_button').on('click', function() {
            var name = $(this).attr("data-name");
            var price = $(this).attr("data-price");
            var type = $(this).attr("data-type");
            var i = $(this).attr("data-index");
            dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
            dataLayer.push({
                "event": "select_item",
                "ecommerce": {
                    "items": [{
                        "item_name": name,
                        "price": price,
                        "item_type": type,
                        "index": i
                    }],
                }
            });
			console.log( JSON.stringify( dataLayer ) );
            setIndexInSession(i);
        });

        if($('body').hasClass('single-product')){
            var index = getIndexFromSession();
            var title = $('.product_title').text();
            var type = $('#item_type').val();
            var price = $('#item_price').val();
            // console.log('title' , title);
            // console.log('type' , type);

            dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
            dataLayer.push({
                "event": " view_item",
                "ecommerce": {
                    "items": [{
                        "item_name": title,
                        "price": price,
                        "item_type": type,
                        "index": index
                    }],
                }
            });
        }

        $('.product_specification_link').on('click', function() {
            var title = $('h1.product_title.entry-title').text();
			if( $(this).text() == 'Kit Specifications' ){
				dataLayer.push({
					'event':'product_page',
					'label_event':'Kit Specifications',
					'item_name':title
				});
			}else{
				dataLayer.push({
					'event':'product_page',
					'label_event':'SOM Specifications',
					'item_name':title
				});
			}
			console.log( JSON.stringify( dataLayer ) );
        });

        /* $( "body" ).on( 'click', '.woocommerce-variation-add-to-cart.woocommerce-variation-add-to-cart-enabled', function(e){
            e.preventDefault();
			
			

        }); */


        /* $( '.single_add_to_cart_button' ).on('click', function(e) {
            
        }); */

        if($('body').hasClass('woocommerce-cart')){
            var itemsArray = [];
            var curr = '';
            var index = getIndexFromSession();
            dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
            $('td.product-name').each(function(){
                var qty = $(this).parent().find('.qty');
                var sku = $(this).attr('data-sku');
                console.log('sku',sku);
                var variant = '';
                if(sku.indexOf('STK') != -1){
                    variant = 'Starter kit';
                }
                if(sku.indexOf('DVK') != -1){
                    variant = 'Development kit';
                }
                 curr = $(this).attr('data-curr');
                var price = $(this).attr('data-price');
                var type = $(this).attr('data-type');
                var index = $(this).attr('data-index');
                var cat4 = $(this).attr('data-cat');
                var system = '';
                var som = '';
                var kit = '';
                var name = $(this).find('a').text();
                var item_type = '';
                som = $(this).attr('data-pa_som');
                if(type == 'kit'){
                    item_type = 'Evaluation Kits';
                    system = $(this).attr('data-pa_system');
                    kit = $(this).attr('data-pa_kit');
                }else if(type == 'accessory'){
                    item_type = "accessory";
                }else{
                    item_type = "System on Module";
                }

                var qtyy = qty.val();
                itemsArray.push({
                    "item_id": sku,
                    "item_name": name,
                    "price": price,
                    "item_type": item_type,
                    "item_variant": variant,
                    "item_category": som,
                    "item_category2": system,
                    "item_category4": cat4,
                    "item_category5": "",
                    "quantity": qtyy,
                    "index": index
                });


            });
            dataLayer.push({
                "event": "view_cart",
                "ecommerce": {
                    "currency": curr,
                    "items": itemsArray
                }
            });
            console.log(dataLayer);

        }

        if($('body').hasClass('woocommerce-checkout')){
            var itemsArray = [];
            var curr = '';
            dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
            $('td.product-name').each(function(){
                var qty = $(this).find('.product-quantity').text();
                qtyy = $.trim(qty);
                var sku = $(this).attr('data-sku');
                console.log('sku',sku);
                var variant = '';
                if(sku.indexOf('STK') != -1){
                    variant = 'Starter kit';
                }
                if(sku.indexOf('DVK') != -1){
                    variant = 'Development kit';
                }
                curr = $(this).attr('data-curr');
                var price = $(this).attr('data-price');
                var type = $(this).attr('data-type');
                var index = $(this).attr('data-index');
                var cat4 = $(this).attr('data-cat');
                var system = '';
                var som = '';
                var kit = '';
                var name = $(this).attr('data-name');;
                var item_type = '';
                som = $(this).attr('data-pa_som');
                if(type == 'kit'){
                    item_type = 'Evaluation Kits';
                    system = $(this).attr('data-pa_system');
                    kit = $(this).attr('data-pa_kit');
                }else if(type == 'accessory'){
                    item_type = "accessory";
                }else{
                    item_type = "System on Module";
                }

           
                itemsArray.push({
                    "item_id": sku,
                    "item_name": name,
                    "price": price,
                    "item_type": item_type,
                    "item_variant": variant,
                    "item_category": som,
                    "item_category2": system,
                    "item_category4": cat4,
                    "item_category5": "",
                    "quantity": qtyy,
                    "index": index
                });


            });
            dataLayer.push({
                "event": "begin_checkout",
                "ecommerce": {
                    "currency": curr,
                    "items": itemsArray
                }
            });
            console.log(dataLayer);

        }


        // jQuery('a.cart-contents').on('click' , function(e){
        //     e.preventDefault();
        //     dataLayer.push({
        //         "event":"cart_popup",
        //         "event_category":"cart_popup",
        //         "event_action":"click",
        //         "event_label":"cart",
        //     });
        //
        // })
        $('body').on('click' , '#cart_button' , function(e){
            dataLayer.push({
                "event":"cart_popup",
                "event_category":"cart_popup",
                "event_action":"click",
                "event_label":"cart",
            });
			console.log( JSON.stringify( dataLayer ) );
        });
        $('body').on('click' , '#check_button' , function(e){
           
            dataLayer.push({
                "event":"cart_popup",
                "event_category":"cart_popup",
                "event_action":"click",
                "event_label":"checkout",
            });
			console.log( JSON.stringify( dataLayer ) );

        });

        // $('body').on('click' , 'a.cart-contents' , function(){
        //     dataLayer.push({
        //         "event":"cart_popup",
        //         "event_category":"cart_popup",
        //         "event_action":"click",
        //         "event_label":"cart",
        //     });
        //
        // });



          $('.woocommerce-cart').find('.product-remove a').click(function() {
        dataLayer.push({'event': 'CartPage-Click-RemoveItem'});
        var td = $(this).parent().parent().find('.product-name');
        var qty = $(this).parent().parent().find('.qty');
        console.log('qty', qty.val());
        console.log('td', td);

        var sku = td.attr('data-sku');
        console.log('sku',sku);
        var variant = '';
        if(sku.indexOf('STK') != -1){
            variant = 'Starter kit';
        }
        if(sku.indexOf('DVK') != -1){
            variant = 'Development kit';
        }
        var curr = td.attr('data-curr');
        var price = td.attr('data-price'); 
        var type = td.attr('data-type');
        var index = td.attr('data-index');
        var cat4 = td.attr('data-cat');
        var system = ''; 
        var som = '';
        var kit = '';
        var name = td.find('a').text();
        var item_type = '';
        som = td.attr('data-pa_som');
        if(type == 'kit'){
            item_type = 'Evaluation Kits';
            system = td.attr('data-pa_system');
            kit = td.attr('data-pa_kit');
        }else if(type == 'accessory'){
            item_type = "accessory";
        }else{
            item_type = "System on Module";
        }

        var qty = qty.val();

        dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
        dataLayer.push({
            "event": "remove_from_cart",
            "ecommerce": {
                "currency": curr,
                "items": [{
                    "item_id": sku,
                    "item_name": name,
                    "price": price,
                    "item_type": item_type,
                    "item_variant": variant,
                    "item_category": som,
                    "item_category2": system,
                    "item_category4": cat4,
                    "item_category5": "",
                    "quantity": qty,
                    "index": index
                }]
            }
        });
        console.log(dataLayer);
    });




    });

    // Set the index value in sessionStorage
    function setIndexInSession(index) {
        sessionStorage.setItem('data_index', index);
    }
    function setCat4Session(index) {
        sessionStorage.setItem('data_cat', index);
    }


    // Get the index value from sessionStorage
    function getIndexFromSession() {
        return sessionStorage.getItem('data_index');
    }
       function getCat4FromSession() {
        return sessionStorage.getItem('data_cat');
    }

})(jQuery);