<?php
/* Include PanaceaApi class */
require_once("panacea_api.php");

$api = new PanaceaApi();
$api->setUsername("demouser");
$api->setPassword("demouser");

$groups = $api->address_book_groups_get_list();

if($api->ok($groups)) {
	foreach($groups['details'] as $group) {
		echo "Group {$group['name']} has ID {$group['id']}\n";
	}
}

/* Let's add a new group */

$result = $api->address_book_group_add("Api demo group");

if($api->ok($result)) {
	echo "Group added! ID = {$result['details']}\n";

	/* Now let's add some contacts to our group */

	$contact1 = $api->address_book_contact_add($result['details'], "441234567"); /* Remember to specify the group ID, first name and last name are optional parameters */
	$contact2 = $api->address_book_contact_add($result['details'], "441234568", "Donald");

	if($api->ok($contact1) && $api->ok($contact2)) {
		echo "Both contacts added successfully, IDs {$contact1['details']} and {$contact2['details']}\n";

		/* Let's set the surname for contact2 */

		$api->address_book_contact_update($contact2['details'], null, null, "Jackson"); /* Specify null to not change */
	}

	/* Let's list all the contacts now */

	$contacts = $api->address_book_contacts_get_list($result['details']);

	/* Let's delete this group now */

	$api->address_book_group_delete($result['details']);
}


?>