<?php
/**
 * Email Footer
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-footer.php.
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
 * @version     2.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>
</div>
</td>
</tr>
</table>
<!-- End Content -->
</td>
</tr>
</table>
<?php
if ( ICL_LANGUAGE_CODE == 'de' ){
    ?>
    <div style="padding: 0 40px; margin:10px;text-align:left; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; font-size: 13px;">
        <b style="color:#0558a7"><?php the_field('emails_footer_shipping_note', 'option'); ?></b>
        <br>
        <br>
        <b  style="color:#0558a7">Danke, dass Sie unsere Produkte eingekauft haben.</b><br />
        Hardware Dokumentation, finden Sie auf der entsprechenden Produktseite auf  <a target="_blank" href="http://www.variscite.de">www.variscite.de</a><br />
        Die Software Dokumentation und Entwicklerunterstützung finden Sie unter <a target="_blank" href="http://www.variwiki.com">www.variwiki.com</a> beschrieben.<br />
        Kontaktieren Sie Ihren Variscite Account Manager für weitere Unterstützung und Zugang zu unserem Kundenportal (Ticket System).</div>
    <?php
} else {
    ?>
    <div style="padding: 0 40px; margin:10px;text-align:left; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; font-size: 13px;">
        <b style="color:#0558a7"><?php the_field('emails_footer_shipping_note', 'option'); ?></b>
        <br>
        <br>
        <b  style="color:#0558a7">Thank you for purchasing our products.</b><br />
        Detailed hardware documentation can be found in the related product pages on our website <a target="_blank" href="http://www.variscite.com">www.variscite.com</a><br />
        Software documentation and developer guides are detailed on <a target="_blank" href="http://www.variwiki.com">www.variwiki.com</a><br />
        For further support and access to the customer portal ticketing system, please approach your Variscite account manager.</div>
    <?php
}
?>
<!-- End Body -->
</td>
</tr>
<tr>
    <td align="center" valign="top">
        <!-- Footer -->
        <table border="0" cellpadding="10" cellspacing="0" width="600" id="template_footer">
            <tr>
                <td valign="top">
                    <table border="0" cellpadding="10" cellspacing="0" width="100%">
                        <tr>
                            <td colspan="2" valign="middle" id="credit">
                                <?php echo wpautop( wp_kses_post( wptexturize( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ) ) ); ?>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- End Footer -->
    </td>
</tr>
</table>
</td>
</tr>
</table>
</div>
</body>
</html>
