<?php
/**
 *
 *		This view prepends the comment form, displays the notice letting users know comments are moderated
 * 		If the user is the content owner, it gives the forms to batch approve/delete comments
 *
 */

$entity_guid = $vars['entity']->guid;

// have to count total number of comments like this because we have a plugin hook
// that modifies the default count to only include visible comments
$tmpcommentcount = $vars['entity']->getAnnotations('generic_comment');
$real_comment_num = count($tmpcommentcount);
$visible_comment_count = elgg_count_comments($vars['entity']);

//bug fixed in 1.7.9 - have to do this for now
if($visible_comment_count == "<span>0</span>"){ $visible_comment_count = 0; }

$comments_to_moderate = $real_comment_num - $visible_comment_count;

// only show the message once
if($mc_notice_count != 1){
	$mc_notice_count = 1;
	

	if((moderated_comments_is_moderated($entity_guid) && !isloggedin()) || (moderated_comments_is_moderated($entity_guid) && $comments_to_moderate > 0 && $vars['entity']->owner_guid == get_loggedin_userid())){
		echo "<div class=\"generic_comment mc_notice\">";
	}


	if(get_loggedin_userid() == $vars['entity']->owner_guid && moderated_comments_is_moderated($entity_guid) && $comments_to_moderate > 0){
		?>
<div class="mc_moderation_control">
	<form id="mcApprovalForm"
		action="<?php echo $vars['url']; ?>mod/moderated_comments/actions/annotation/review.php"
		method="post">
		<input id="mcApprovalID" type="hidden" name="id" value=""> <input
			type="hidden" name="action" value="approve"> <input type="submit"
			value="<?php echo elgg_echo('moderated_comments:approve_checked'); ?>">
	</form>
</div>
<div class="mc_moderation_control">
	<form id="mcDeleteForm"
		action="<?php echo $vars['url']; ?>mod/moderated_comments/actions/annotation/review.php"
		method="post">
		<input id="mcDeleteID" type="hidden" name="id" value=""> <input
			type="hidden" name="action" value="delete"> <input type="submit"
			value="<?php echo elgg_echo('moderated_comments:delete_checked'); ?>"
			onclick="return confirm('<?php echo elgg_echo('moderated_comments:delete_confirm'); ?>');">
	</form>
</div>
<script type="text/javascript">
	var idarray = new Array();
	</script>
		<?php
	}

/*
 * Remove option to toggle moderation
 *
	if($vars['entity']->owner_guid == get_loggedin_userid() && $vars['entity']->access_id != ACCESS_PUBLIC){
		echo "<div class=\"mc_moderation_control\">";
		echo "<form action=\"" . $vars['url'] . "mod/moderated_comments/actions/entity/moderate_toggle.php\" method=\"post\">";

		if(moderated_comments_is_moderated($entity_guid)){
			$value = elgg_echo('moderated_comments:disable');
			$action = "off";
		}
		else{
			$value = elgg_echo('moderated_comments:enable');
			$action = "on";
		}
		echo "<input type=\"hidden\" name=\"id\" value=\"$entity_guid\">";
		echo "<input type=\"hidden\" name=\"action\" value=\"$action\">";
		echo "<input type=\"submit\" value=\"$value\">";
		echo "</form>";
		echo "</div>";
	}
*/
	if(moderated_comments_is_moderated($entity_guid) && !isloggedin()){
		echo "<div style=\"clear: both\">" . elgg_echo('moderated_comments:moderated_notice') . "</div>";
	}

	if((moderated_comments_is_moderated($entity_guid) && !isloggedin()) || (moderated_comments_is_moderated($entity_guid) && $comments_to_moderate > 0 && $vars['entity']->owner_guid == get_loggedin_userid())){
		echo "<div class=\"mc_clear_div\"></div>";
		echo "</div>";
	}
	?>

	<?php
} // end if $mc_notice_count
?>