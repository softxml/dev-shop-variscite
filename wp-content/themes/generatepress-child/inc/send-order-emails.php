<?php
//send mail to different emails(admin)

//new order email trigger
add_action( 'vari_trigger_new_order_email', 'vari_send_new_order_mails_diff_receipts', 10, 1 );
//add_action( 'woocommerce_order_status_pending_to_processing_notification', 'vari_send_new_order_mails_diff_receipts' );
/* add_action( 'woocommerce_order_status_pending_to_completed_notification', 'vari_send_new_order_mails_diff_receipts', 11, 2 );
add_action( 'woocommerce_order_status_pending_to_on-hold_notification', 'vari_send_new_order_mails_diff_receipts', 11, 2 );
add_action( 'woocommerce_order_status_failed_to_processing_notification', 'vari_send_new_order_mails_diff_receipts', 11, 2 );
add_action( 'woocommerce_order_status_failed_to_completed_notification', 'vari_send_new_order_mails_diff_receipts', 11, 2 );
add_action( 'woocommerce_order_status_failed_to_on-hold_notification', 'vari_send_new_order_mails_diff_receipts', 11, 2 );
add_action( 'woocommerce_order_status_cancelled_to_processing_notification', 'vari_send_new_order_mails_diff_receipts', 11, 2 );
add_action( 'woocommerce_order_status_cancelled_to_completed_notification', 'vari_send_new_order_mails_diff_receipts', 11, 2 );
add_action( 'woocommerce_order_status_cancelled_to_on-hold_notification', 'vari_send_new_order_mails_diff_receipts', 11, 2 ); */

function vari_send_new_order_mails_diff_receipts( $order_id ){
	$fa = fopen( ABSPATH."mail-diff_receipts-dbg.log", "a+" );
	fwrite( $fa, print_r( $order_id, true ) );
	fwrite( $fa, PHP_EOL );
	fwrite( $fa, PHP_EOL );
	fclose( $fa );
	
	//check if mail already sent
	/* $emailSent = get_post_meta( $order_id, 'processing_email_sent', true );
	if( $emailSent == "yes" ){
		return;
	}
	
	update_post_meta( $order_id, 'processing_email_sent', 'yes' ); */
	
	//add filter of new email receipts
	add_filter( 'woocommerce_email_recipient_new_order', 'vari_add_email_receipts', 11, 3 );
	
	add_filter( 'wp_mail', 'vari_add_email_headers', 11, 1 );
	
	add_filter( 'woocommerce_new_order_email_allows_resend', 'vari_allow_resend_new_order_emails', 10, 3 );
	
	//trigger new order email
	$email_new_order = WC()->mailer()->get_emails()['WC_Email_New_Order'];
	$email_new_order->trigger( $order_id );
	
	//remove filter of new email receipts
	remove_filter( 'woocommerce_email_recipient_new_order', 'vari_add_email_receipts', 10, 3 );
	
	remove_filter( 'wp_mail', 'vari_add_email_headers', 11, 1 );
	
	//remove filter to allow resend emails
	remove_filter( 'woocommerce_new_order_email_allows_resend', 'vari_allow_resend_new_order_emails', 10, 3 );
}

//cancelled order email trigger
add_action( 'woocommerce_order_status_processing_to_cancelled_notification', 'vari_send_cancelled_order_mails_diff_receipts', 11, 2 );
add_action( 'woocommerce_order_status_on-hold_to_cancelled_notification', 'vari_send_cancelled_order_mails_diff_receipts', 11, 2 );
function vari_send_cancelled_order_mails_diff_receipts( $order_id, $order = false ){
	//add filter of new email receipts
	add_filter( 'woocommerce_email_recipient_cancelled_order', 'vari_add_email_receipts', 10, 3 );
	
	add_filter( 'wp_mail', 'vari_add_email_headers', 11, 1 );
	
	//trigger new order email
	$email_new_order = WC()->mailer()->get_emails()['WC_Email_Cancelled_Order'];
	$email_new_order->trigger( $order_id );
	
	remove_filter( 'wp_mail', 'vari_add_email_headers', 11, 1 );
	
	//remove filter of new email receipts
	remove_filter( 'woocommerce_email_recipient_cancelled_order', 'vari_add_email_receipts', 10, 3 );
}

//failed order email trigger
add_action( 'woocommerce_order_status_pending_to_failed_notification', 'vari_send_failed_order_mails_diff_receipts', 11, 2 );
add_action( 'woocommerce_order_status_on-hold_to_failed_notification', 'vari_send_failed_order_mails_diff_receipts', 11, 2 );
function vari_send_failed_order_mails_diff_receipts( $order_id, $order = false ){
	//add filter of new email receipts
	add_filter( 'woocommerce_email_recipient_failed_order', 'vari_add_email_receipts', 10, 3 );
	
	add_filter( 'wp_mail', 'vari_add_email_headers', 11, 1 );
	
	//trigger new order email
	$email_new_order = WC()->mailer()->get_emails()['WC_Email_Failed_Order'];
	$email_new_order->trigger( $order_id );
	
	remove_filter( 'wp_mail', 'vari_add_email_headers', 11, 1 );
	
	//remove filter of new email receipts
	remove_filter( 'woocommerce_email_recipient_failed_order', 'vari_add_email_receipts', 10, 3 );
}

//list out new email receipts
function vari_add_email_receipts( $recipient, $object, $instance ){
	$recipient = 'eden.d@variscite.com,ayelet.o@variscite.com,lena.g@variscite.com,michalchemo@gmail.com';
	return $recipient;
}

//allow resend new order emails
function vari_allow_resend_new_order_emails(){
	return true;
}

function vari_add_email_headers( $params ){
	$newHeaders = $params['headers']."To: eden.d@variscite.com,ayelet.o@variscite.com,lena.g@variscite.com,michalchemo@gmail.com";
	$params['headers'] = $newHeaders;
	
	$fa = fopen( ABSPATH."mail-params-dbg.log", "a+" );
	fwrite( $fa, print_r( $params, true ) );
	fwrite( $fa, PHP_EOL );
	fwrite( $fa, PHP_EOL );
	fclose( $fa );
	
	return $params;
}