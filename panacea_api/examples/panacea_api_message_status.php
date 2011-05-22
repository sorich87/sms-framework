<?php
require_once("panacea_api.php");

$api = new PanaceaApi();
$api->setUsername("donald");
$api->setPassword("abc123");

$status = $api->message_status("533f2f43-8ef4-46c4-0107-12300000000f");

if($api->ok($status)) {
	var_dump($status);
}

?>