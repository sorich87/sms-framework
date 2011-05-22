<?php
/**
 * @package SMS_Messages
 * @version 0.2
 */

/**
 * Send SMS Messages
 *
 * @since 0.2
 **/
class IS_SM_Sms {
	/**
	 * Stores SMS api object
	 *
	 * @since 0.2
	 **/
	var $api;

	/**
	 * Class contructor
	 *
	 * @since 0.2
	 **/
	public function __construct( $username, $password ) {
		require_once( IS_SM_DIR . 'includes/class-panaceaapi.php' );

		$api = new PanaceaApi();
		$api->setUsername( $username );
		$api->setPassword( $password );

		$api->performActionsImmediately( false );

		$this->api = $api;
	}

	/**
	 * Send sms message
	 *
	 * @since 0.2
	 **/
	public function send( $message, $to, $from ) {
		return $this->api->message_send( $to, $message, $from );
	}

	/**
	 * Send sms message
	 *
	 * @since 0.2
	 **/
	public function process() {
		return $this->api->execute_multiple();
	}
}
