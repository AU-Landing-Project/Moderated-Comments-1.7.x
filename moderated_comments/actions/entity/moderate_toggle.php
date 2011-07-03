<?php
/**
 * 		This action accepts $_POST variables for id and action
 * 		id is the guid of the entity
 * 		action is either "on" or "off" - to denote turning moderation on/off
 *
 * 		Checks in place to make sure the user is logged in, and has permission to moderate
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . "/engine/start.php");

gatekeeper(); // must be logged in

$entity = get_entity($_REQUEST['id']);
$action = $_REQUEST['action'];

if($entity->owner_guid != get_loggedin_userid()){	// not the owner of the content, send them away
	register_error(elgg_echo('moderated_comments:wrong_permissions'));
	forward(REFERRER);
}

if($action == "on"){	// turn on moderation for this entity
	if(!moderated_comments_is_moderated($entity->guid)){
		$entity->is_moderated = true;
		system_message(elgg_echo('moderated_comments:moderation_on'));
	}
	else{
		register_error(elgg_echo('moderated_comments:moderation_already_on'));
	}
}

if($action == "off"){	// turn on moderation for this entity
	if(moderated_comments_is_moderated($entity->guid)){
		$entity->is_moderated = false;
		$entity->unmoderated_comments = "";
		system_message(elgg_echo('moderated_comments:moderation_off'));
	}
	else{
		register_error(elgg_echo('moderated_comments:moderation_already_off'));
	}
}

forward(REFERRER);