<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' ); ?>

<form class="woocommerce-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
    <?php do_action( 'woocommerce_before_cart_table' ); ?>

    <table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
        <thead>
        <tr>
            <th class="product-thumbnail">&nbsp;</th>
            <th class="product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
<!--            <th class="product-price">--><?php //esc_html_e( 'Price', 'woocommerce' ); ?><!--</th>-->
            <th class="product-quantity"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
            <th class="product-subtotal"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
            <th class="product_edit">&nbsp;</th>
            <th class="product-remove">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <?php do_action( 'woocommerce_before_cart_contents' ); ?>

        <?php
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

            if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                ?>
                <tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">



                    <td class="product-thumbnail">
                        <?php
                        $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

                        if ( ! $product_permalink ) {
                            echo $thumbnail; // PHPCS: XSS ok.
                        } else {
                            printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // PHPCS: XSS ok.
                        }
                        ?>
                    </td>

                    <?php
                        $product_type = determine_product_type($_product);
						$oproduct = $_product->get_data();
                        $prod_id = $oproduct["id"];
                        $parent_id = wp_get_post_parent_id($prod_id);
                        $oindex = get_field('pro_index' , $parent_id);
                        $cat4 = get_field('cat4' , $parent_id);
                        $sku = $oproduct["sku"];
                        $price = $oproduct["price"];
                        $curr =  get_woocommerce_currency($oproduct["id"]);
                        if( $product_type == 'kit' ){
                            $pa_kit = $oproduct["attributes"]["pa_kit"];
                            $pa_som = $oproduct["attributes"]["pa_som-configuration"];
                            $pa_system = $oproduct["attributes"]["pa_operating-system"];
							?>
                            <td class="product-name" data-type="<?php echo $product_type; ?>" data-cat = "<?php echo $cat4; ?>" data-price="<?php echo $price; ?>" data-sku="<?php echo $sku; ?>" data-curr="<?php echo $curr; ?>" data-pa_system="<?php echo $pa_system; ?>" data-pa_som="<?php echo $pa_som; ?>" data-pa_kit="<?php echo $pa_kit; ?>" data-index = "<?php echo $oindex; ?>"  data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
							<?php
                        }elseif($product_type == 'som'){
                            $pa_som = $oproduct["attributes"]["pa_som-configuration"];
                            ?>
                            <td class="product-name" data-type="<?php echo $product_type; ?>" data-cat = "<?php echo $cat4; ?>" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>" data-price="<?php echo $price; ?>" data-sku="<?php echo $sku; ?>" data-curr="<?php echo $curr; ?>" data-pa_som="<?php echo $pa_som; ?>" data-index = "<?php echo $oindex; ?>">
							<?php
                        }else{
                            $accessory_variation = '';
                            if( isset( $oproduct["attributes"]["pa_connector-type"] ) ){
                                $accessory_variation = $oproduct["attributes"]["pa_connector-type"];
                            }
							if( isset( $oproduct["attributes"]["attribute_pa_compatible-som"] ) ){
								$accessory_variation = $oproduct["attributes"]["attribute_pa_compatible-som"];
							}
                            ?>
                            <td class="product-name" data-type="<?php echo $product_type; ?>" data-cat = "<?php echo $cat4; ?>" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>" data-price="<?php echo $price; ?>" data-sku="<?php echo $sku; ?>" data-curr="<?php echo $curr; ?>" data-pa_som="<?php echo $accessory_variation; ?>" data-index = "<?php echo $oindex; ?>" >
                        <?php } ?>
                        <div class="mobile_product_thumbnail">
                            <?php
                            $thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                            echo $thumbnail; // PHPCS: XSS ok.

                            ?>
                        </div>
                        <?php
                        if ( ! $product_permalink ) {
                            echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' );
                        } else {
                            echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
                        }

                        do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );

                        // Meta data.
                        echo wc_get_formatted_cart_item_data( $cart_item ); // PHPCS: XSS ok.

                        // Backorder notification.
                        if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
                            echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
                        }
                        ?>
                    </td>

<!--                    <td class="product-price" data-title="--><?php //esc_attr_e( 'Price', 'woocommerce' ); ?><!--">-->
<!--                        --><?php
//                        echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
//                        ?>
<!--                    </td>-->

                    <td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
                        <?php
                        echo apply_filters( 'variscite_woocommerce_cart_item_quantity', $cart_item_key, $cart_item,$_product);

//                        if ( $_product->is_sold_individually() ) {
//                            $product_quantity = sprintf( '1 <input type="hidden" name="cart[%s][qty]" value="1" />', $cart_item_key );
//                        } else {
//                            $product_quantity = woocommerce_quantity_input( array(
//                                'input_name'   => "cart[{$cart_item_key}][qty]",
//                                'input_value'  => $cart_item['quantity'],
//                                'max_value'    => $_product->get_max_purchase_quantity(),
//                                'min_value'    => '0',
//                                'product_name' => $_product->get_name(),
//                            ), $_product, false );
//                        }
//
//                        echo apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item ); // PHPCS: XSS ok.
//                        if( get_post_meta( $product_id, '_wc_max_qty_product', true ) ){
//                            echo '<div class="max_qty_product">' . get_post_meta( $product_id, '_wc_max_qty_product', true ) . ' items max</div>';
//                        }

                        ?>
                    </td>

                    <td class="product-subtotal" data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>">
                        <?php
                        echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
                        ?>
                    </td>

                    <td class="product_edit <?php if( $_product->is_type( 'simple' ) ) echo 'simple'; ?>">
                        <?php
                            if( !$_product->is_type( 'simple' ) ) {
                                printf( '<a href="%s" class="edit">%s</a>', esc_url( $product_permalink ), __('Edit') );
                            }
                    ?>
                    </td>

                    <td class="product-remove <?php if( $_product->is_type( 'simple' ) ) echo 'simple'; ?>">
                        <?php
                        // @codingStandardsIgnoreLine
                        echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
                            '<a href="%s" class="remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">'.__("Remove").'</a>',
                            esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                            __( 'Remove this item', 'woocommerce' ),
                            esc_attr( $product_id ),
                            esc_attr( $_product->get_sku() )
                        ), $cart_item_key );
                        ?>
                    </td>

                </tr>
                <?php
            }
        }
        ?>

        <?php do_action( 'woocommerce_cart_contents' ); ?>

        <tr class="wc_cart_totals order_subtotal">
            <td colspan="2" class="empty_column"></td>
            <td class="wc_cart_total_title"><?php _e("Subtotal")?></td>
            <td  class="wc_cart_total_amount" colspan="3" class="actions">
                <?php echo WC()->cart->get_cart_subtotal(); ?>
            </td>
        </tr>

        <tr class="wc_cart_totals shipping_subtotal">
            <td colspan="2" class="empty_column"></td>

            <td class="wc_cart_total_title"><?php _e('Shipping','Variscite')?></td>
            <td class="wc_cart_total_amount" colspan="3" class="actions">
                <span class="shipping_desc"><?php _e("Based on your ZIP Code")?></php></span>
            </td>
        </tr>

        <tr class="wc_cart_totals">
            <td colspan="2" class="empty_column"></td>
            <td class="wc_cart_total_title"><?php _e("Total") ?></td>
            <td  class="wc_cart_total_amount" colspan="3" class="actions">
                <?php echo WC()->cart->get_cart_total(); ?>
            </td>
        </tr>

        <tr>
            <td colspan="6" class="actions">

                <?php if ( wc_coupons_enabled() ) { ?>
                    <div class="coupon">
                        <label for="coupon_code"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label> <input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" /> <button type="submit" class="button" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?></button>
                        <?php do_action( 'woocommerce_cart_coupon' ); ?>
                    </div>
                <?php } ?>

                <button type="submit" class="button" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"><?php esc_html_e( 'Update cart', 'woocommerce' ); ?></button>

                <?php do_action( 'woocommerce_cart_actions' ); ?>

                <?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
            </td>
        </tr>

        <?php do_action( 'woocommerce_after_cart_contents' ); ?>
        </tbody>
    </table>
    <?php do_action( 'woocommerce_after_cart_table' ); ?>
</form>

<div class="cart-collaterals">
    <?php
    /**
     * Cart collaterals hook.
     *
     * @hooked woocommerce_cross_sell_display
     * @hooked woocommerce_cart_totals - 10
     */
    do_action( 'woocommerce_cart_collaterals' );
    ?>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
