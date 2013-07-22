<?php

if (!check_forumperm($ForumID)) {
	error(403);
}

$ForumID = (int)db_string($_GET['forumid']);
$Subscriptions = get_subscribed_forums($LoggedUser['ID']);
if ($_GET['do'] == 'subscribe') {
	add_subscribed_forum($UserID, $ForumID);
} elseif ($_GET['do'] == 'unsubscribe') {
	remove_subscribed_forum($UserID, $ForumID);
}
header('Location: forums.php?action=viewforum&forumid=' . $ForumID);
?>

