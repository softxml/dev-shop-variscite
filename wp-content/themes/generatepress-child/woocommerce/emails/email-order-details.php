<?php
/**
 * Order details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates/Emails
 * @version 3.3.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$text_align = is_rtl() ? 'right' : 'left';

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>

<h2>
    <?php
    $order_received_page = get_home_url() . '/checkout/order-received/' . $order->get_id() . '/?key=' . $order->order_key . '&utm_nooverride=1&count=no';

    if ( $sent_to_admin ) {
        $before = '<a class="link" href="' . esc_url($order_received_page) . '">';
        $after  = '</a>';
    } else {
        $before = '';
        $after  = '';
    }
    /* translators: %s: Order ID. */
    echo wp_kses_post( $before . sprintf( __( '[Order #%s]', 'woocommerce' ) . $after . ' (<time datetime="%s">%s</time>)', $order->get_order_number(), $order->get_date_created()->format( 'c' ), wc_format_datetime( $order->get_date_created() ) ) );
    ?>
</h2>

<div style="margin-bottom: 40px;">
    <table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
        <thead>
        <tr>
            <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
            <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Price per Unit', 'woocommerce' ); ?></th>
            <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
            <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
        </tr>
        </thead>
        <tbody>

        <?php
        foreach($order->get_items() as $item_id => $item):

            $product_id = $item->get_product_id();
            $item_product = wc_get_product($product_id);

            if($item_product->is_type('simple')) {
                $price_p_unit = $item_product->get_price();
                $_product = $item_product;
            } else {
                $product_variation = new WC_Product_Variation($item->get_variation_id());
                $_product = $product_variation;

                $price_p_unit = $product_variation->regular_price;
            }

            $currency_code = $order->get_currency();
            $currency_symbol = get_woocommerce_currency_symbol( $currency_code );

            $product_type = determine_product_type($_product);

            $pn = $_product->get_sku();

            $returned_template = '';

            if($product_type === 'kit') {
                $som_slug = $_product->attributes['pa_som-configuration'];
                $included_som = get_term_by('slug', $som_slug, 'pa_som-configuration');

                // Operating System
                $os = $_product->attributes['pa_operating-system'];
                $included_os = get_term_by('slug', $os, 'pa_operating-system');

                // SOM Part Number
                $som_pn = get_field('system_on_module_pn', 'pa_som-configuration_' . $included_som->term_id);

                $returned_template .= '<li><strong class="wc-item-meta-label">Kit PN:</strong><p>' . $pn . '</p></li>';

                if(! $sent_to_admin) {
                    $returned_template .= '<li><strong class="wc-item-meta-label">Included SOM:</strong><p>' . $included_som->name . '</p></li>';
                    $returned_template .= '<li><strong class="wc-item-meta-label">SOM PN:</strong><p>' . $som_pn . '</p></li>';
                    $returned_template .= '<li><strong class="wc-item-meta-label">Operating System:</strong><p>' . $included_os->name . '</p></li>';
                }

            } else if($product_type === 'som') {
                $som_slug = $_product->attributes['pa_som-configuration'];
                $included_som = get_term_by('slug', $som_slug, 'pa_som-configuration');

                if(! $sent_to_admin) {
                    $returned_template .= '<li><p>' . $included_som->name . '</p></li>';
                }

                $returned_template .= '<li><strong class="wc-item-meta-label">Module PN:</strong><p>' . $pn . '</p></li>';

            } else {
                $returned_template .= '<li><strong class="wc-item-meta-label">PN:</strong><p>' . $pn . '</p></li>';
            }

            ?>

            <tr class="order_item">
                <td class="td" style="width: 40%; color: #636363; border: 1px solid #e5e5e5; vertical-align: middle; padding: 12px; text-align: left; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap: break-word;">
                    <?php
                        if(! $sent_to_admin) {
                            echo $item->get_name() . '<br>';
                        }
                    ?>

                    <ul style="font-size: 13px; padding: 0; list-style-type: none;">
                        <?php echo $returned_template; ?>
                    </ul>
                </td>

                <td class="td" style="width: 20%; color: #636363; border: 1px solid #e5e5e5; vertical-align: middle; padding: 12px; text-align: left; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                    <span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol"><?php echo $currency_symbol; ?></span><?php echo number_format((float)$price_p_unit, 2, '.', ''); ?></span>
                </td>

                <td class="td" style="width: 20%; color: #636363; border: 1px solid #e5e5e5; vertical-align: middle; padding: 12px; text-align: left; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                    <?php echo $item->get_quantity(); ?>
                 </td>

                <td class="td" style="width: 20%; color: #636363; border: 1px solid #e5e5e5; vertical-align: middle; padding: 12px; text-align: left; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
                    <span class="woocommerce-Price-amount amount"><span class="woocommerce-Price-currencySymbol"><?php echo $currency_symbol; ?></span><?php echo number_format((float)$item->get_total(), 2, '.', ''); ?></span>
                </td>
            </tr>

        <?php endforeach; ?>

        <?php
//        var_dump(wc_get_email_order_items( $order, array( // WPCS: XSS ok.
//            'show_sku'      => $sent_to_admin,
//            'show_image'    => false,
//            'image_size'    => array( 32, 32 ),
//            'plain_text'    => $plain_text,
//            'sent_to_admin' => $sent_to_admin,
//        ) ));
        ?>
        </tbody>
        <tfoot>
        <?php
        $totals = $order->get_order_item_totals();

        if ( $totals ) {
            $i = 0;
            foreach ( $totals as $total ) {
                $i++;
                ?>
                <tr>
                    <th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post( $total['label'] ); ?></th>
                    <td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>" colspan="2"><?php echo wp_kses_post( $total['value'] ); ?></td>
                </tr>
                <?php
            }
        }
        ?>
        </tfoot>
    </table>

    <?php if ( $order->get_customer_note() ): ?>

    <div class="customer-notes" style="margin-top: 24px;">
        <strong>Customer Notes:</strong>
        <p><?php echo wp_kses_post( wptexturize( $order->get_customer_note() ) ); ?></p>
    </div>

    <?php endif; ?>

    </div>

<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>
