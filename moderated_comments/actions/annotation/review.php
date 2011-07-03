<?php
/**
 * 		This action accepts $_GET variables for id and action
 * 		id is the unique id of the annotation
 * 		action is either "approve" or "delete" - self explanatory
 *
 * 		Checks in place to make sure the user is logged in, and has permission to moderate
 */

require_once(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . "/engine/start.php");

gatekeeper(); // must be logged in

// build array of comment ids to remove
$mc_comment_array = array();
if(!is_numeric($_REQUEST['id'])){
	// comma delimited list from checkboxes
	$mc_comment_array = explode(',', $_REQUEST['id']);
}
else{
	$mc_comment_array[0] = $_REQUEST['id'];
}

$annotation = get_annotation($mc_comment_array[0]);
$entity = get_entity($annotation->entity_guid);

if($entity->owner_guid != get_loggedin_userid()){ // logged in user isn't the owner of the entity, send them away
	register_error(elgg_echo('moderated_comments:wrong_permissions'));
	forward(REFERRER);
}

if(!moderated_comments_is_moderated($entity->guid)){  // this entity isn't being moderated, send them away
	register_error(elgg_echo('moderated_comments:entity_unmoderated'));
	forward(REFERRER);	
}


// get array of all unmoderated comments
$review_array = explode(',', $entity->unmoderated_comments);

// strip out the comments being moderated
for($i=0; $i<count($mc_comment_array); $i++){
	$review_array = removeFromArray($mc_comment_array[$i], $review_array);
}

//save the new array
moderated_comments_save_array($review_array, $entity);

// delete comments if requested
if($_REQUEST['action'] == "delete"){
	for($i=0; $i<count($mc_comment_array); $i++){
		$annotation = get_annotation($mc_comment_array[$i]);
		$annotation->delete();
	}
	system_message(elgg_echo('moderated_comments:deleted'));
}

// set a system message
if($_REQUEST['action'] == "approve"){
	system_message(elgg_echo('moderated_comments:approved'));
}

forward($entity->getURL());