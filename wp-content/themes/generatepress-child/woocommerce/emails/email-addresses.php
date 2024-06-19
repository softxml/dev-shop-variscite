<?php
/**
 * Email Addresses
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-addresses.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see 	    https://docs.woocommerce.com/document/template-structure/
 * @author 		WooThemes
 * @package 	WooCommerce/Templates/Emails
 * @version     3.2.1
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$text_align = is_rtl() ? 'right' : 'left';

$firstName = __('First Name' , 'var-email');
$lastName = __('Last Name' , 'var-email');
$company = __('Company' , 'var-email');
$email = __('Email' , 'var-email');
$phone = __('Phone' , 'var-email');
$address1 = __('Address' , 'var-email');
$address2 = __('Address Line 2' , 'var-email');
$zipCode = __('ZIP Code' , 'var-email');
$city = __('City' , 'var-email');
$state = __('State' , 'var-email');
$country = __('Country' , 'var-email');
$comp_reg_num = __('Company registration number' , 'var-email');

?>

<div style="padding: 5px 0;">
    <strong><?php _e( 'EORI number:', 'woocommerce' ); ?></strong>
    <span><?php echo get_post_meta( $order->get_id(), '_billing_eori', true ); ?></span>
</div>

<div style="padding: 24px 0;">
    <strong><?php _e( 'Estimated Project Quantities:', 'woocommerce' ); ?></strong>
    <span><?php echo get_post_meta( $order->get_id(), '_estimated_product_quantities', true ); ?></span>
</div>

<table id="addresses" cellspacing="0" cellpadding="0" style="width: 100%; vertical-align: top; margin-bottom: 40px; padding:0;" border="0">
    <tr>
        <td style="text-align:<?php echo $text_align; ?>; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; border:0; padding:0;" valign="top" width="50%">
            <h2><?php _e( 'Billing address', 'woocommerce' ); ?></h2>

            <address class="address">

                <?php
                $address = array(
                    $firstName => $order->get_billing_first_name(),
                    $lastName => $order->get_billing_last_name(),
                    $company => $order->get_billing_company(),
                    $email => $order->get_billing_email(),
                    $phone => $order->get_billing_phone(),
                    $address1  => $order->get_billing_address_1(),
                    $address2 => $order->get_billing_address_2(),
                    $zipCode  => $order->get_billing_postcode(),
                    $city => $order->get_billing_city(),
                    $state => $order->get_billing_state(),
                    $country => WC()->countries->countries[$order->get_billing_country()]
                );

                $billing_reg_number = get_post_meta( $order->get_id(), '_billing_company_reg_number', true);

                if($billing_reg_number) {
                    $address['Company registration number'] = $billing_reg_number;
                }

                foreach($address as $addr_key => $addr) {
                    if(! empty($addr)) {
                        echo esc_html($addr_key) . ': ' . esc_html($addr) . '<br/>';
                    }
                }
                ?>
            </address>
        </td>

        <!--        --><?php //if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && ( $shipping = $order->get_formatted_shipping_address() ) ) : ?>
        <td style="text-align:<?php echo $text_align; ?>; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; padding:0;" valign="top" width="50%">
            <h2><?php _e( 'Shipping address', 'woocommerce' ); ?></h2>

            <address class="address">
                <?php
                $address = array(
                    $firstName => $order->get_shipping_first_name(),
                    $lastName => $order->get_shipping_last_name(),
                    $company => $order->get_shipping_company(),
                    $email => '',
                    $phone => '',
                    $address1 => $order->get_shipping_address_1(),
                    $address2 => $order->get_shipping_address_2(),
                    $zipCode => $order->get_shipping_postcode(),
                    $city => $order->get_shipping_city(),
                    $state => $order->get_shipping_state(),
                    $country => WC()->countries->countries[$order->get_shipping_country()]
                );

                $shipping_email = get_post_meta( $order->get_id(), '_shipping_email', true );
                $shipping_phone = get_post_meta( $order->get_id(), '_shipping_phone', true);

                if($shipping_email) {
                    $address['Email'] = $shipping_email;
                }

                if($shipping_phone) {
                    $address['Phone'] = $shipping_phone;
                }

                if($shipping_reg_number) {
                    $address['Company registration number'] = $shipping_reg_number;
                }

                foreach($address as $addr_key => $addr) {
                    if(! empty($addr)) {
                        echo esc_html($addr_key) . ': ' . esc_html($addr) . '<br/>';
                    }
                }
                ?>
            </address>
        </td>
        <!--        --><?php //endif; ?>
    </tr>
</table>
