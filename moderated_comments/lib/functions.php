<?php

//
//	called when a comment is made, checks if object is moderated
//	if so adds to moderation list
//
function moderated_comments_check($event, $object_type, $obj){
	global $CONFIG;
	
	if(moderated_comments_is_moderated($obj->entity_guid) && !isloggedin()){
		$entity = get_entity($obj->entity_guid);
		if($entity->owner_guid != get_loggedin_userid()){
			moderated_comments_add_to_review_list($obj);
		}
	}

}


//
//	this function adds the new comment id to the list that needs to be checked
//
function moderated_comments_add_to_review_list($obj){
	global $CONFIG;

	$entity = get_entity($obj->entity_guid);
	
	$review_array = explode(',', $entity->unmoderated_comments);

	// add new comment id to array
	if(!is_array($review_array)){
		$review_array = array();
	}
	
	if(!in_array($obj->id, $review_array)){
		$review_array[] = $obj->id;
	}

	// save the new array
	moderated_comments_save_array($review_array, $entity);

	system_message(elgg_echo('moderated_comments:comment_success'));
}


//
//	this function saves the array as a list of ids separated by commas
//
function moderated_comments_save_array($review_array, $entity){
	$context = get_context();
	set_context('moderated_comments');
	sort($review_array);
	//convert new array back into a list
	$review_list = implode(',', $review_array);

	//save the list
	$entity->unmoderated_comments = $review_list;
	
	set_context($context);
}


//
//	this function returns true if the entity is being moderated
//
function moderated_comments_is_moderated($id){
	$entity = get_entity($id);
	
	if(!is_object($entity)){
		return false;
	}
	
	if($entity->is_moderated || $entity->access_id == ACCESS_PUBLIC){
		return true;
	}
	
	return false;
}

//
// This function checks each object on creation (called by event handler)
//	if object has public access, set to moderated
//
function moderated_comments_entity_create($event, $object_type, $object){
	if($object_type == "object"){
		if($object->access_id == ACCESS_PUBLIC){
			$object->is_moderated = true;
		}
		else{
			$object->is_moderated = false;
		}
	}
}



//
//	This function checks if the entity is being moderated, if so we need to count
// and return the number of APPROVED comments, not total comments
// called by commments:count plugin hook
//
function moderated_comments_comment_count($hook, $type, $returnvalue, $params){
	if(moderated_comments_is_moderated($params['entity']->guid)){
		// get array of total comments
		$comments = $params['entity']->getAnnotations('generic_comment');
		// get array of comments awaiting review
		$unreviewed = explode(',', $params['entity']->unmoderated_comments);
		
		$count = 0;
		for($i=0; $i<count($comments); $i++){
			$id = $comments[$i]->id;	
			if(!empty($id)){		
				if(!in_array($id, $unreviewed)){
					$count++;  // the comment isn't in our list to review, so count it as real	
				}
			}
		}
		
		// careful here, it won't accept 0, considered false
		// and defaults to the actual count
		// wrap 0 in span tags so it renders properly but passes the !empty() test
		// not my ideal solution, because something may want to do math with it
		// it feels like an ugly hack, but I don't think the Elgg devs considered
		// that 0 might be a legit return value.
		// but it works for our purpose...
		// ticket submitted to elgg devs, fixed for 1.7.9
		$elggversion=(int) preg_replace("/[^0-9\s]/","",get_version(true));
		if($elggversion < 179){
			if($count == 0) { $count = "<span>0</span>"; }
		}
		return $count;
	}
}


// permissions check
function moderated_comments_permissions_check(){
	$context = get_context();
	if($context == "moderated_comments"){
		return true;
	}
	
	return NULL;
}


//
//	removes a single item from an array
//	resets keys
//
function removeFromArray($value, $array){
	if(!is_array($array)){ return $array; }
	if(!in_array($value, $array)){ return $array; }
	
	for($i=0; $i<count($array); $i++){
		if($value == $array[$i]){
			unset($array[$i]);
			$array = array_values($array);
		}
	}
	
	return $array;
}