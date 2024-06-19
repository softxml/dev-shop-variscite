// var product_config = JSON.parse('<?php echo json_encode($product_config, JSON_UNESCAPED_SLASHES); ?>');

jQuery('.single_add_to_cart_button').on('click', function(e) {

    var form    = jQuery(this).parents('form'),
        var_id  = form.find('[name="variation_id"]').val();

    window.dataLayer = window.dataLayer || [];

    if(product_config.vars && Object.keys(product_config.vars).length > 0) {

        window.dataLayer.push({
            "event": "addToCart",
            "ecommerce": {
                "currencyCode": "USD",
                "add": {
                    "products": [{
                        "id": product_config.id,
                        "name": product_config.name,
                        "price": product_config.price,
                        "brand": "Variscite",
                        "category": product_config.type,
                        "variant": product_config.vars[var_id],
                        "quantity": 1
                    }]
                }
            }
        });

    } else {

        window.dataLayer.push({
            "event": "addToCart",
            "ecommerce": {
                "currencyCode": "USD",
                "add": {
                    "products": [{
                        "id": product_config.id,
                        "name": product_config.name,
                        "price": product_config.price,
                        "brand": "Variscite",
                        "category": product_config.type,
                        "quantity": 1
                    }]
                }
            }
        });
    }
});