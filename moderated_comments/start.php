<?php
/**
 * Moderated Comments
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU Public License version 2
 * @author Matt Beckett
 * @copyright University of Athabasca 2011
 */

include_once 'lib/functions.php';

function moderated_comments_init() {

	// Load system configuration
	global $CONFIG;	

	// Extend system CSS with our own styles
	elgg_extend_view('metatags','moderated_comments/metatags');

	// Load the language file
	register_translations($CONFIG->pluginspath . "moderated_comments/languages/");

	//register action to approve/delete comments
	register_action("annotation/review", true, $CONFIG->pluginspath . "moderated_comments/actions/annotation/review.php");

	//register action to toggle moderation
	register_action("entity/moderate_toggle", true, $CONFIG->pluginspath . "moderated_comments/actions/entity/moderate_toggle.php");

	// register action to replace core actions/comments/add.php - have to in order to modify notification  :(
	register_action("comments/add", false, $CONFIG->pluginspath . "moderated_comments/actions/comments/add.php");
	
	// register plugin hook to monitor comment counts - return only the count of approved comments
	register_plugin_hook('comments:count', 'all', 'moderated_comments_comment_count', 1000); 
	
    // override permissions for the moderated_comments context
	register_plugin_hook('permissions_check', 'all', 'moderated_comments_permissions_check');	
	
	// register cron hook
    register_plugin_hook('cron', 'hourly', 'moderated_comments_cron');
}


global $CONFIG;

// call init
register_elgg_event_handler('init','system','moderated_comments_init');

// check if newly created comment needs to be reviewed
register_elgg_event_handler('create','annotation','moderated_comments_check');

// check if newly created entity is public - if so moderate
register_elgg_event_handler('create','all','moderated_comments_entity_create');

// check if newly updated entity is public - if so moderate
register_elgg_event_handler('update','all','moderated_comments_entity_create');


/*
 * 	If this was installed and there is existing public content then it won't be moderated
 * 	This checks if cron has run in the last hour, or ever, and if not, then it does some
 * 	work making them all moderated.
 * 	Called on system shutdown so things don't get slowed down.
 */
	//see if this is our first run, or if cron hasn't run in over an hour
	$cron = get_plugin_setting('cron', 'moderated_comments');
	$hour_ago = time() - (60*65); //giving 5 min leeway in case of slow cron, or crontrigger
	
	if(!is_numeric($cron) || $cron < $hour_ago){
		// it's our first run, or cron isn't working, so we're converting any currently public
		// entities to moderated status
		register_elgg_event_handler('shutdown', 'system', 'moderated_comments_check_all_public');
	}

// extend the form view to present a notice that comments are moderated
elgg_extend_view('comments/forms/edit', 'comments/forms/moderated_comments_pre_edit', 0);

?>
