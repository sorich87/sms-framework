<?php
/**
 * @package SMS_Messages
 * @version 0.2
 */
/*
Plugin Name: SMS Messages
Plugin URI: http://intside.com/
Description: Send SMS messages to your site users via several SMS gateways.
Author: Intside
Version: 0.2
Author URI: http://intside.com/
*/

define( 'IS_SM_DIR', plugin_dir_path( __FILE__ ) );
define( 'IS_SM_URL', plugin_dir_url( __FILE__ ) );

require_once( IS_SM_DIR . 'includes/class-sms.php' );

/**
 * Main plugin class
 *
 * @since 0.1
 **/
class IS_SM_Sms_Framework {

	/**
	 * Are we authenticated with SMS API?
	 *
	 * @since 0.2
	 **/
	public $auth = false;

	/**
	 * Store sms object
	 *
	 * @since 0.2
	 **/
	public $sms;

	/**
	 * Class contructor
	 *
	 * @since 0.1
	 **/
	public function __construct() {
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_pages' ) );
		add_action( 'plugins_loaded', array( $this, 'load' ) );
		add_action( 'admin_init', array( $this, 'process' ) );
	}

	/**
	 * Load SMS class
	 *
	 * @since 0.2
	 **/
	public function load() {
		$options = get_option( 'is_sm_options' );

		if ( ! empty( $options['username'] ) && ! empty( $options['password'] ) ) {
			$this->sms  = new IS_SM_Sms( $options['username'], $options['password'] );
			$this->auth = true;
		}
	}

	/**
	 * Process all queued SMS before template is loaded
	 *
	 * @since 0.2
	 **/
	public function process() {
		if ( $this->auth )
			$this->sms->process();
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
		$input['username']         =  wp_filter_nohtml_kses( $input['username'] );
		$input['password']         =  wp_filter_nohtml_kses( $input['password'] );
		$input['user_meta']        =  wp_filter_nohtml_kses( $input['user_meta'] );
		$input['sender']           =  floatval( $input['sender'] );
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
		settings_fields( 'is_sm_options_group' );

		$defaults = array(
			'username'           => '',
			'password'	         => '',
			'user_meta'          => '',
			'sender'	         => '',
			'registration_sms'   => '',
			'registration_email' => ''
		);
		$options = wp_parse_args( get_option( 'is_sm_options' ), $defaults );
		?>
		<h3><?php _e( 'API Authentication' ); ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e( 'Username' ); ?></th>
				<td><input type="text" name="is_sm_options[username]" value="<?php echo esc_attr( $options['username'] ); ?>" class="all-options" /></td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e( 'Password' ); ?></th>
				<td><input type="password" name="is_sm_options[password]" value="<?php echo esc_attr( $options['password'] ); ?>" class="all-options" /></td>
			</tr>
		</table>

		<h3><?php _e( 'Options' ); ?></h3>
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
			<tr valign="top">
				<th scope="row"><?php _e( 'Also send welcome email' ); ?></th>
				<td><input type="checkbox" name="is_sm_options[registration_email]" value="1"<?php checked( '1', $options['registration_email'] ); ?>/></td>
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
		$last_name  = get_user_meta( $user_id, 'last_name', true );

		if ( get_user_meta( $user_id, '_is_sm_registration_sms', true ) != '' ) {
			$message = str_replace( array( '%first_name%', '%last_name%' ), array( $first_name, $last_name ), $options['registration_sms'] );
			$to      = get_user_meta( $user_id, $options['user_meta'], true );
			$from    = $options['sender'];

			if ( $result = $this->sms->send( $message, $to, $from ) )
				update_user_meta( $user_id, '_is_sm_registration_sms', $result );
		}
	}
}

new IS_SM_Sms_Framework;


if ( ! function_exists('wp_new_user_notification') ) :
/**
 * Notify the blog admin of a new user, normally via email and notify user via sms.
 *
 * @since 0.2
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

	$options = get_option( 'is_sm_options' );

	if ( '1' == $options['registration_email'] ) {
		$message  = sprintf(__('Username: %s'), $user_login) . "\r\n";
		$message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n";
		$message .= wp_login_url() . "\r\n";

		wp_mail($user_email, sprintf(__('[%s] Your username and password'), $blogname), $message);
	}

	IS_SM_Sms_Framework::send_registration_sms( $user_id );
}
endif;
