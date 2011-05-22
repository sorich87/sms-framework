<?php
require_once("panacea_api.php");
$api = new PanaceaApi();
$api->setUsername("demouser");
$api->setPassword("demouser");

$file = "../generic/mybatch.csv";

$result = $api->batch_create("My batch name", $file);

if($api->ok($result)) {
	/* Batch created ! */

	$batch_id = $result['details'];

	echo "Batch created with ID {$batch_id}\n";

	/* Let's create another one from XLS */

	$file = "../generic/mybatch.xls";

	$result = $api->batch_create("My second batch", $file, 0, false, 'xls');

	if($api->ok($result)) {
		echo "XLS Batch created!\n";

		/* Give it a moment to parse */
		sleep(1);

		$status = $api->batch_check_status($result['details']);






		if($status['details']['status'] == 32) { // Is it currently paused and waiting?
			$api->batch_start($result['details']);

			/* Let's wait a while and see if it's done */

			sleep(2);

			$status = $api->batch_check_status($result['details']);

		}

	}


}

?>