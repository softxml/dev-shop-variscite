<?php
/*
Plugin Name: Error Mail Notifications
Description: Sends email notifications to a list of emails - edit in mu-plugins folder
*/

add_filter( 'recovery_mode_email', function( $email ) {
	$email['to'] = array('allonsacks@gmail.com', 'ayelet.o@variscite.com', 'lena.g@variscite.com', 'eden.d@variscite.com' , 'roi.c69@gmail.com');
	return $email;
} );