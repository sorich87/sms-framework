<?php
/* Include PanaceaApi class */
require_once("panacea_api.php");

$api = new PanaceaApi();
$api->setUsername("demouser");
$api->setPassword("demouser");

$result = $api->message_send("27111234567", "Hello !", "27111234564");

if($api->ok($result)) {
	echo "Message sent! ID was {$result['details']}\n";
} else {
	/* There was an error */
	echo $api->getError();
}

?>
