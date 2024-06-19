<?php
/**
 * Thankyou page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/thankyou.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.2.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div class="woocommerce-order thankyou_section">

    <?php if ( $order ) : ?>

        <?php if ( $order->has_status( 'failed' ) ) : ?>

            <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed"><?php _e( 'Unfortunately your order cannot be processed as the originating bank/merchant has declined your transaction. Please attempt your purchase again.', 'woocommerce' ); ?></p>

            <p class="woocommerce-notice woocommerce-notice--error woocommerce-thankyou-order-failed-actions">
                <a href="<?php echo esc_url( $order->get_checkout_payment_url() ); ?>" class="button pay"><?php _e( 'Pay', 'woocommerce' ) ?></a>
                <?php if ( is_user_logged_in() ) : ?>
                    <a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="button pay"><?php _e( 'My account', 'woocommerce' ); ?></a>
                <?php endif; ?>
            </p>

        <?php else : ?>

            <p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', __( 'Thank you. Your order has been received.', 'woocommerce' ), $order ); ?></p>

            <ul class="woocommerce-order-overview woocommerce-thankyou-order-details order_details">

                <li class="woocommerce-order-overview__order order">
                    <?php _e( 'Order number:', 'woocommerce' ); ?>
                    <span><?php echo $order->get_order_number(); ?></span>
                </li>

                <li class="woocommerce-order-overview__date date">
                    <?php _e( 'Date:', 'woocommerce' ); ?>
                    <span><?php echo wc_format_datetime( $order->get_date_created() ); ?></span>
                </li>

                <?php if ( is_user_logged_in() && $order->get_user_id() === get_current_user_id() && $order->get_billing_email() ) : ?>
                    <li class="woocommerce-order-overview__email email">
                        <?php _e( 'Email:', 'woocommerce' ); ?>
                        <span><?php echo $order->get_billing_email(); ?></span>
                    </li>
                <?php endif; ?>

                <li class="woocommerce-order-overview__total total">
                    <?php _e( 'Total:', 'woocommerce' ); ?>
                    <span><?php echo $order->get_formatted_order_total(); ?></span>
                </li>

                <?php if ( $order->get_payment_method_title() ) : ?>
                    <li class="woocommerce-order-overview__payment-method method">
                        <?php _e( 'Payment method:', 'woocommerce' ); ?>
                        <span><?php echo wp_kses_post( $order->get_payment_method_title() ); ?></span>
                    </li>
                <?php endif; ?>

            </ul>
            <?php
            if ( ICL_LANGUAGE_CODE == 'de' ){
                ?>  <div class="thankyou_desc">
                    Hardware Dokumentation, finden Sie auf <u><a href="https://variscite.de" target="_blank">www.variscite.de</a></u><br>
                    Die Software Dokumentation und Entwicklerunterstützung finden Sie unter <u><a href="https://variwiki.com" target="_blank">www.variwiki.com</a></u> beschrieben<br>
                    Kontaktieren Sie Ihren Variscite Account Manager für weitere Unterstützung und Zugang zu unserem Kundenportal (Ticket System).
                </div> <?php
            } else {
                ?>  <div class="thankyou_desc">
                    Detailed hardware documentation can be found on <u><a href="https://variscite.com" target="_blank">www.variscite.com</a></u><br>
                    Software documentation and developer guides are detailed on <u><a href="https://variwiki.com" target="_blank">www.variwiki.com</a></u><br>
                    For further support and access to the customer portal ticketing system, please approach your Variscite account manager
                </div> <?php
            }
            ?>

        <?php endif; ?>

        <?php do_action( 'woocommerce_thankyou_' . $order->get_payment_method(), $order->get_id() ); ?>
        <?php do_action( 'woocommerce_thankyou', $order->get_id() ); ?>

    <?php else : ?>

        <p class="woocommerce-notice woocommerce-notice--success woocommerce-thankyou-order-received"><?php echo apply_filters( 'woocommerce_thankyou_order_received_text', __( 'Thank you. Your order has been received.', 'woocommerce' ), null ); ?></p>

    <?php endif; ?>

</div>
