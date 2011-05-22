<?php
/**
 * @package SMS_Messages
 * @version 0.1
 */
/*
Plugin Name: SMS Messages
Plugin URI: http://intside.com/
Description: Send SMS messages to your site users via several SMS gateways.
Author: Intside
Version: 0.1
Author URI: http://intside.com/
*/

/**
 * Main plugin class
 *
 * @since 0.1
 **/
class IS_SM_Sms_Messages {

	/**
	 * Class contructor
	 *
	 * @since 0.1
	 **/
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_pages' ) );

		global $panacea_api;

		require_once( plugin_dir_path( __FILE__ ) . 'panacea_api/panacea_api.php' );

		$panacea_api = new PanaceaApi();
		$panacea_api->setUsername( 'sorich87' );
		$panacea_api->setPassword( 'xxxxxxx' );

		$panacea_api->performActionsImmediately(false);
	}

	/**
	 * Options Init
	 *
	 * @since 0.1
	 **/
	public function register_settings() {
		 register_setting( 'is_sm_options_group', 'is_sm_options', array( $this, 'is_sm_options_validate' ) );
	}

	/**
	 * Add administration menus
	 *
	 * @since 0.1
	 **/
	public function add_admin_pages() {
		add_options_page( __( 'SMS Settings' ), __( 'SMS' ), 'manage_options', 'sms-settings', array( $this, 'options_page' ) );
	}

	/**
	 * Sanitize data entered by user on options page
	 *
	 * @since 0.1
	 **/
	public function is_sm_options_validate( $input ) {
		$input['user_meta']       =  wp_filter_nohtml_kses( $input['user_meta'] );
		$input['sender'] =  floatval( $input['sender'] );
		$input['registration_sms'] = stripslashes( wp_filter_nohtml_kses( $input['registration_sms'] ) );

		return $input;
	}

	/**
	 * Content of the settings page
	 *
	 * @since 0.1
	 **/
	public function options_page() {
		if ( ! current_user_can( 'manage_options' ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
?>

<div class="wrap">
	<h2><?php _e( 'SMS Settings' ); ?></h2>
	<form method="post" action="options.php">
		<?php
		$user_name = 'test';
		$random_password = wp_generate_password( 12, false );
		$user_email = 'sorich87+test@gmail.com';
		$user_id = wp_create_user( $user_name, $random_password, $user_email );

		settings_fields( 'is_sm_options_group' );
		$defaults = array(
			'user_meta'        => '',
			'sender'	       => '',
			'registration_sms' => ''
		);
		$options = wp_parse_args( get_option( 'is_sm_options' ), $defaults );
		?>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e( 'Name of the user meta where phone numbers are stored' ); ?></th>
				<td><input type="text" name="is_sm_options[user_meta]" value="<?php echo esc_attr( $options['user_meta'] ); ?>" class="all-options" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Sender phone number (with country code)' ); ?></th>
				<td><input type="text" name="is_sm_options[sender]" value="<?php echo esc_attr( $options['sender'] ); ?>" class="all-options" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'SMS text to send to user on registration' ); ?></th>
				<td><textarea name="is_sm_options[registration_sms]" class="all-options" rows="5"><?php echo esc_textarea( $options['registration_sms'] ); ?></textarea></td>
			</tr>
		</table>
		<p class="submit">
			<input type="submit" class="button-primary" value="<?php _e( 'Save Changes' ); ?>" />
		</p>
	</form>
</div>

<?php
	}

	/**
	 * Send sms message on registration
	 *
	 * @since 0.1
	 **/
	public function send_registration_sms( $user_id ) {
		$options = get_option( 'is_sm_options' );

		$first_name = get_user_meta( $user_id, 'first_name', true );

		if ( get_user_meta( $user_id, '_is_sm_registration_sms', true ) !== false ) {
			$message = str_replace( '%first_name%', $first_name, $options['registration_sms'] );
			$to      = get_user_meta( $user_id, $options['user_meta'], true );
			$from    = $options['sender'];

			if ( $result = $this->sms( $message, $to, $from ) )
				update_user_meta( $user_id, '_is_sm_registration_sms', $result );
		}
	}

	/**
	 * Send sms message
	 *
	 * @since 0.1
	 **/
	public function sms( $message, $to, $from ) {
		global $panacea_api;

		$panacea_api->message_send( $to, $message, $from );
	}

	/**
	 * Send sms message
	 *
	 * @since 0.1
	 **/
	public function process() {
		global $panacea_api;

		return $results = $panacea_api->execute_multiple();
	}
}

new IS_SM_Sms_Messages;

if ( !function_exists('wp_new_user_notification') ) :
/**
 * Notify the blog admin of a new user, normally via email.
 *
 * @since 2.0
 *
 * @param int $user_id User ID
 * @param string $plaintext_pass Optional. The user's plaintext password
 */
function wp_new_user_notification($user_id, $plaintext_pass = '') {
	$user = new WP_User($user_id);

	$user_login = stripslashes($user->user_login);
	$user_email = stripslashes($user->user_email);

	// The blogname option is escaped with esc_html on the way into the database in sanitize_option
	// we want to reverse this for the plain text arena of emails.
	$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);

	$message  = sprintf(__('New user registration on your site %s:'), $blogname) . "\r\n\r\n";
	$message .= sprintf(__('Username: %s'), $user_login) . "\r\n\r\n";
	$message .= sprintf(__('E-mail: %s'), $user_email) . "\r\n";

	@wp_mail(get_option('admin_email'), sprintf(__('[%s] New User Registration'), $blogname), $message);

	if ( empty($plaintext_pass) )
		return;

	$message  = sprintf(__('Username: %s'), $user_login) . "\r\n";
	$message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n";
	$message .= wp_login_url() . "\r\n";

	wp_mail($user_email, sprintf(__('[%s] Your username and password'), $blogname), $message);

}
endif;
