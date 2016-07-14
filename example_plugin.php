<?php

/**
 * Plugin Name: Example Plugin
 * Plugin URI: https://www.beaverlodgehq.com
 * Description: Example plugin to use with the Page Builder Extensions Plugin.
 * Version: 0.5
 * Author: Beaverlodge HQ
 * Author URI: https://www.beaverlodgehq.com
 */

/* replace custom with correct case */
/* replace CUSTOM with correct case */

define( 'SW_CUSTOM_STORE_URL', 'https://beaverlodgehq.com' );
define( 'SW_CUSTOM_ITEM_NAME', '404 Page' );
define( 'SW_CUSTOM_VERSION', '1.0.0' );
define( 'SW_CUSTOM_AUTHOR', 'Beaverlodge HQ' );


function sw_custom_plugin_updater() {

	$license_key = trim( get_option( 'edd_custom_license_key' ) );

	$edd_updater = new EDD_SL_Plugin_Updater( SW_CUSTOM_STORE_URL, __FILE__, array(
			'version' 	=> SW_CUSTOM_VERSION,
			'license' 	=> $license_key,
			'item_name' => SW_CUSTOM_ITEM_NAME,
			'author' 	=> SW_CUSTOM_AUTHOR
		)
	);

}
add_action( 'admin_init', 'sw_custom_plugin_updater', 0 );

function sw_custom_license_link() {

	?>
	<button type="button" class="btn btn-default"><a href="#custom"><?php echo SW_CUSTOM_ITEM_NAME; ?> License</a></button>
	<?php
}
add_action( 'page_builder_extension_licence_link', 'sw_custom_license_link' );

function sw_custom_license() {	
	$license 	= get_option( 'edd_custom_license_key' );
	$status 	= get_option( 'edd_custom_license_status' );
	?>
	<div class="col-xs-12" id="custom">
	<h3><?php echo SW_CUSTOM_ITEM_NAME; ?>  License</h3>
		<form method="post" action="options.php">

			<?php settings_fields('edd_custom_license'); ?>

			<table class="form-table">
				<tbody>
					<tr valign="top">
						<th scope="row" valign="top">
							<?php _e('Enter License'); ?>
						</th>
						<td>
							<input id="edd_custom_license_key" name="edd_custom_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
						</td>
					</tr>
					<?php if( false !== $license ) { ?>
						<tr valign="top">
							<th scope="row" valign="top">
								<?php _e('Activate License'); ?>
							</th>
							<td>
								<?php if( $status !== false && $status == 'valid' ) { ?>
									<span style="color:green;"><?php _e('active'); ?></span>
									<?php wp_nonce_field( 'edd_custom_nonce', 'edd_custom_nonce' ); ?>
									<input type="submit" class="btn-danger" name="edd_license_deactivate" value="<?php _e('Deactivate License'); ?>"/>
								<?php } else {
									wp_nonce_field( 'edd_custom_nonce', 'edd_custom_nonce' ); ?>
									<input type="submit" class="btn-success" name="edd_license_activate" value="<?php _e('Activate License'); ?>"/>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>
			<?php submit_button(); ?>

		</form>
		</div>
	<?php
}
add_action( 'page_builder_extension_licenses', 'sw_custom_license' );

function edd_custom_register_option() {
	register_setting('edd_custom_license', 'edd_custom_license_key', 'edd_custom_sanitize_license' );
}
add_action('admin_init', 'edd_custom_register_option');

function edd_custom_sanitize_license( $new ) {
	$old = get_option( 'edd_custom_license_key' );
	if( $old && $old != $new ) {
		delete_option( 'edd_custom_license_status' );
	}
	return $new;
}

function edd_custom_activate_license() {

	if( isset( $_POST['edd_license_activate'] ) ) {

	 	if( ! check_admin_referer( 'edd_custom_nonce', 'edd_custom_nonce' ) )
			return; 

		$license = trim( get_option( 'edd_custom_license_key' ) );

		$api_params = array(
			'edd_action'=> 'activate_license',
			'license' 	=> $license,
			'item_name' => urlencode( SW_CUSTOM_ITEM_NAME ),
			'url'       => home_url()
		);

		$response = wp_remote_post( SW_CUSTOM_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		if ( is_wp_custom( $response ) )
			return false;
		
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		update_option( 'edd_custom_license_status', $license_data->license );

	}
}
add_action('admin_init', 'edd_custom_activate_license');

function edd_custom_deactivate_license() {

	if( isset( $_POST['edd_license_deactivate'] ) ) {

	 	if( ! check_admin_referer( 'edd_custom_nonce', 'edd_custom_nonce' ) )
			return; 

		$license = trim( get_option( 'edd_custom_license_key' ) );

		$api_params = array(
			'edd_action'=> 'deactivate_license',
			'license' 	=> $license,
			'item_name' => urlencode( SW_CUSTOM_ITEM_NAME ),
			'url'       => home_url()
		);

		$response = wp_remote_post( SW_CUSTOM_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		if ( is_wp_custom( $response ) )
			return false;

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		if( $license_data->license == 'deactivated' )
			delete_option( 'edd_custom_license_status' );

	}
}
add_action('admin_init', 'edd_custom_deactivate_license');

function edd_custom_check_license() {

	global $wp_version;

	$license = trim( get_option( 'edd_custom_license_key' ) );

	$api_params = array(
		'edd_action' => 'check_license',
		'license' => $license,
		'item_name' => urlencode( SW_CUSTOM_ITEM_NAME ),
		'url'       => home_url()
	);

	$response = wp_remote_post( SW_CUSTOM_STORE_URL, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

	if ( is_wp_custom( $response ) )
		return false;

	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	if( $license_data->license == 'valid' ) {
		echo 'valid'; exit;
	} else {
		echo 'invalid'; exit;
	}
}
