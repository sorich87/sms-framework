<?php
/* Include PanaceaApi class */
require_once("panacea_api.php");

$api = new PanaceaApi();
$api->setUsername("demouser");
$api->setPassword("demouser");

$result = $api->message_send("27111234567", "Hello", "27111234456");

if($api->ok($result)) {
	echo "Message sent! ID was {$result['details']}\n";
	$message_id = $result['details'];

	/* Now we can check what the status of the message is */

	$message = $api->message_status($message_id);

	if($api->ok($message)) {
		echo "Message cost was {$message['details']['cost']}\n";
		echo "Message had {$message['details']['parts']} parts\n";
		echo "Message current status is {$message['details']['status']}\n";
	}

	/* Let's check our balance */

	$balance = $api->user_get_balance();

	if($api->ok($message)) {
		echo "Our current balance is {$balance['details']}\n";
	}

	$groups = $api->address_book_groups_get_list();

	/* Get a list of our batches */
	$batches = $api->batches_list();

	if($api->ok($batches)) {
		if(!empty($batches['details'])) {
			/* We have some batches, let's start the first one */

			$api->batch_start($batches['details'][0]['id']);

		}
	}


} else {
	/* There was an error */
	echo $api->getError();
}

?>