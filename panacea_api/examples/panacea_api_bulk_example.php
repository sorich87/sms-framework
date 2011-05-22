<?php
require_once("panacea_api.php");

/* This sample demonstrates how to send multiple actions to the API in a single request */

$api = new PanaceaApi();
$api->setUsername("demouser");
$api->setPassword("demouser");

/* Now we must change mode from immediate to delayed */

$api->performActionsImmediately(false);

/* Let's queue our requests */
$api->message_send("27111234567", "Message 1", "27111234567");
$api->message_send("27111234568", "Message 2", "27111234568");
$api->message_send("27111234569", "Message 3", "27111234569");

/* Now let's send them */
$results = $api->execute_multiple();

if($api->ok($results)) {
	/* API Received the requests, we can now process each individual action's result */

	foreach($results['details'] as $result) {
		if($api->ok($result)) {
			/* Successful result */

		} else {

		}
	}

}

?>