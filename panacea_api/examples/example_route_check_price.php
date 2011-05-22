<?php
/* Include PanaceaApi class */
require_once("../panacea_api.php");

$api = new PanaceaApi();
$api->setUsername("demo");
$api->setPassword("demo");

$result = $api->route_check_price("27111234567");

if($api->ok($result)) {
	echo "Cost is ".$result['details']['cost']."\n";
	echo "Route status is ".$result['details']['status']."\n";
	echo "Route status (reason) ".$result['details']['reason']."\n";

} else {
	echo "Error occurred\n";
}
