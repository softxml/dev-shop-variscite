<?php
//add settings for sf details
add_action( 'admin_menu', 'variscite_settings_for_sf' );
function variscite_settings_for_sf(){
	add_menu_page(
		__( 'SF Details', 'variscite-salesforce' ),
		__( 'SF Details', 'variscite-salesforce' ),
		'manage_options',
		'varscite-sf-details',
		'variscite_settings_options_cb',
		'dashicons-admin-generic',
		5
	);
}

//plugin settings options callback
function variscite_settings_options_cb(){
	?>
	<div class="wrap">
		<h1><?php _e( 'SF Details', 'variscite-salesforce' ); ?></h1>
		<form action='options.php' method='post'>
			<?php
			settings_fields( 'variscite_sf_details_page' );
			do_settings_sections( 'variscite_sf_details_page' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

//register settings for plugins
add_action( 'admin_init', 'variscite_register_settings_cb' );
function variscite_register_settings_cb(){
	register_setting( 'variscite_sf_details_page', 'variscite_sfdc_settings' );
	add_settings_section(
		'variscite_sf_details_section',
		__( 'A simple plugin that sends data to salesforce.', 'variscite-salesforce' ),
		'variscite_information_section_cb',
		'variscite_sf_details_page'
	);
	
	//header top ads script
	add_settings_field(
		'variscite_sfdc_username',
		__( 'SFDC Username', 'variscite-salesforce' ),
		'variscite_sfdc_username_cb',
		'variscite_sf_details_page',
		'variscite_sf_details_section'
	);

	//header top ads script
	add_settings_field(
		'variscite_sfdc_password',
		__( 'SFDC Password', 'variscite-salesforce' ),
		'variscite_sfdc_password_cb',
		'variscite_sf_details_page',
		'variscite_sf_details_section'
	);
	
	//header top ads script
	add_settings_field(
		'variscite_sfdc_client_id',
		__( 'SFDC Client ID', 'variscite-salesforce' ),
		'variscite_sfdc_client_id_cb',
		'variscite_sf_details_page',
		'variscite_sf_details_section'
	);
	
	//header top ads script
	add_settings_field(
		'variscite_sfdc_client_secret',
		__( 'SFDC Client Secret', 'variscite-salesforce' ),
		'variscite_sfdc_client_secret_cb',
		'variscite_sf_details_page',
		'variscite_sf_details_section'
	);
	
	//header top ads script
	add_settings_field(
		'variscite_sfdc_url',
		__( 'SFDC URL', 'variscite-salesforce' ),
		'variscite_sfdc_url_cb',
		'variscite_sf_details_page',
		'variscite_sf_details_section'
	);
}

//plugin page information section
function variscite_information_section_cb() {
	
}

//sfdc username callback
function variscite_sfdc_username_cb(){
	$settings = get_option( 'variscite_sfdc_settings' );
	?>
	<input type="text" name="variscite_sfdc_settings[variscite_sfdc_username]" value="<?php echo $settings['variscite_sfdc_username']; ?>" class="regular-text" />
    <?php
}

//sfdc password callback
function variscite_sfdc_password_cb(){
	$settings = get_option( 'variscite_sfdc_settings' );
	?>
	<input type="text" name="variscite_sfdc_settings[variscite_sfdc_password]" value="<?php echo $settings['variscite_sfdc_password']; ?>" class="regular-text" />
	<p class="description"><?php _e( 'Field value is encrypted', 'variscite-salesforce' ); ?></p>
    <?php
}

//sfdc client id callback
function variscite_sfdc_client_id_cb(){
	$settings = get_option( 'variscite_sfdc_settings' );
	?>
	<input type="text" name="variscite_sfdc_settings[variscite_sfdc_client_id]" value="<?php echo $settings['variscite_sfdc_client_id']; ?>" class="regular-text" />
	<p class="description"><?php _e( 'Field value is encrypted', 'variscite-salesforce' ); ?></p>
    <?php
}

//sfdc client secret callback
function variscite_sfdc_client_secret_cb(){
	$settings = get_option( 'variscite_sfdc_settings' );
	?>
	<input type="text" name="variscite_sfdc_settings[variscite_sfdc_client_secret]" value="<?php echo $settings['variscite_sfdc_client_secret']; ?>" class="regular-text" />
	<p class="description"><?php _e( 'Field value is encrypted', 'variscite-salesforce' ); ?></p>
    <?php
}

//sfdc url callback
function variscite_sfdc_url_cb(){
	$settings = get_option( 'variscite_sfdc_settings' );
	?>
	<input type="text" name="variscite_sfdc_settings[variscite_sfdc_url]" value="<?php echo $settings['variscite_sfdc_url']; ?>" class="regular-text" />
    <?php
}

//function for encrypt data
function vari_encrypt_data( $plainText ){
	$secretKey = md5( 'varisecret' );
	$iv = substr( hash( 'sha256', "aaaabbbbcccccddddeweee" ), 0, 16 );
	$encryptedText = openssl_encrypt( $plainText, 'AES-128-CBC', $secretKey, OPENSSL_RAW_DATA, $iv );
	return base64_encode( $encryptedText );
}

//function for decrypt data
function vari_decrypt_data( $encryptedText ){
	$key = md5( 'varisecret' );
	$iv = substr( hash( 'sha256', "aaaabbbbcccccddddeweee" ), 0, 16 );
	$decryptedText = openssl_decrypt( base64_decode( $encryptedText ), 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv );
	return $decryptedText;
}

//encrypt data before saving options
add_action( 'update_option_variscite_sfdc_settings', 'vari_encrypt_data_cb', 99, 2 );
function vari_encrypt_data_cb( $old_value, $new_value ){
	if( isset( $new_value['variscite_sfdc_password'] ) && !empty( $new_value['variscite_sfdc_password'] ) ){
		$new_value['variscite_sfdc_password'] = vari_encrypt_data( $new_value['variscite_sfdc_password'] );
	}
	if( isset( $new_value['variscite_sfdc_client_id'] ) && !empty( $new_value['variscite_sfdc_client_id'] ) ){
		$new_value['variscite_sfdc_client_id'] = vari_encrypt_data( $new_value['variscite_sfdc_client_id'] );
	}
	if( isset( $new_value['variscite_sfdc_client_secret'] ) && !empty( $new_value['variscite_sfdc_client_secret'] ) ){
		$new_value['variscite_sfdc_client_secret'] = vari_encrypt_data( $new_value['variscite_sfdc_client_secret'] );
	}
	
	remove_action( 'update_option_variscite_sfdc_settings', 'vari_encrypt_data_cb', 99, 2 );
	update_option( 'variscite_sfdc_settings', $new_value );
	add_action( 'update_option_variscite_sfdc_settings', 'vari_encrypt_data_cb', 99, 2 );
}