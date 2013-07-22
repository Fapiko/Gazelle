<?
function get_thread_info($ThreadID, $Return = true, $SelectiveCache = false, $ApiCall = false) {
	global $DB, $Cache;
	if ((!$ThreadInfo = $Cache->get_value('thread_'.$ThreadID.'_info')) || !isset($ThreadInfo['OP'])) {
		$DB->query("
			SELECT
				t.Title,
				t.ForumID,
				t.IsLocked,
				t.IsSticky,
				COUNT(fp.id) AS Posts,
				t.LastPostAuthorID,
				ISNULL(p.TopicID) AS NoPoll,
				t.StickyPostID,
				t.AuthorID as OP,
				t.Ranking
			FROM forums_topics AS t
				JOIN forums_posts AS fp ON fp.TopicID = t.ID
				LEFT JOIN forums_polls AS p ON p.TopicID=t.ID
			WHERE t.ID = '$ThreadID'
			GROUP BY fp.TopicID");
		if (!$DB->has_results()) {
			if (!$ApiCall) {
				error(404);
			} else {
				return null;
			}
		}
		$ThreadInfo = $DB->next_record(MYSQLI_ASSOC, false);
		if ($ThreadInfo['StickyPostID']) {
			$ThreadInfo['Posts']--;
			$DB->query("
				SELECT
					p.ID,
					p.AuthorID,
					p.AddedTime,
					p.Body,
					p.EditedUserID,
					p.EditedTime,
					ed.Username
				FROM forums_posts as p
					LEFT JOIN users_main AS ed ON ed.ID = p.EditedUserID
				WHERE p.TopicID = '$ThreadID'
					AND p.ID = '".$ThreadInfo['StickyPostID']."'");
			list($ThreadInfo['StickyPost']) = $DB->to_array(false, MYSQLI_ASSOC);
		}
		if (!$SelectiveCache || !$ThreadInfo['IsLocked'] || $ThreadInfo['IsSticky']) {
			$Cache->cache_value('thread_'.$ThreadID.'_info', $ThreadInfo, 0);
		}
	}
	if ($Return) {
		return $ThreadInfo;
	}
}

function check_forumperm($ForumID, $Perm = 'Read') {
	global $LoggedUser, $Forums;
	if ($LoggedUser['CustomForums'][$ForumID] == 1) {
		return true;
	}
	if ($Forums[$ForumID]['MinClass'.$Perm] > $LoggedUser['Class'] && (!isset($LoggedUser['CustomForums'][$ForumID]) || $LoggedUser['CustomForums'][$ForumID] == 0)) {
		return false;
	}
	if (isset($LoggedUser['CustomForums'][$ForumID]) && $LoggedUser['CustomForums'][$ForumID] == 0) {
		return false;
	}
	return true;
}

// Function to get basic information on a forum
// Uses class CACHE
function get_forum_info($ForumID) {
	global $DB, $Cache;
	$Forum = $Cache->get_value('ForumInfo_'.$ForumID);
	if (!$Forum) {
		$DB->query("
			SELECT
				Name,
				MinClassRead,
				MinClassWrite,
				MinClassCreate,
				COUNT(forums_topics.ID) AS Topics
			FROM forums
				LEFT JOIN forums_topics ON forums_topics.ForumID=forums.ID
			WHERE forums.ID='$ForumID'
			GROUP BY ForumID");
		if (!$DB->has_results()) {
			return false;
		}
		// Makes an array, with $Forum['Name'], etc.
		$Forum = $DB->next_record(MYSQLI_ASSOC);

		$Cache->cache_value('ForumInfo_'.$ForumID, $Forum, 86400); // Cache for a day
	}
	return $Forum;
}

function get_forums() {
	global $DB, $Cache;
	if (!$Forums = $Cache->get_value('forums_list')) {
	$DB->query('
		SELECT
			f.ID,
			f.CategoryID,
			f.Name,
			f.Description,
			f.MinClassRead,
			f.MinClassWrite,
			f.MinClassCreate,
			f.NumTopics,
			f.NumPosts,
			f.LastPostID,
			f.LastPostAuthorID,
			f.LastPostTopicID,
			f.LastPostTime,
			COUNT(sr.ThreadID) AS SpecificRules,
			t.Title,
			t.IsLocked,
			t.IsSticky
		FROM forums AS f
			JOIN forums_categories AS fc ON fc.ID = f.CategoryID
			LEFT JOIN forums_topics as t ON t.ID = f.LastPostTopicID
			LEFT JOIN forums_specific_rules AS sr ON sr.ForumID = f.ID
		GROUP BY f.ID
		ORDER BY fc.Sort, fc.Name, f.CategoryID, f.Sort');
	$Forums = $DB->to_array('ID', MYSQLI_ASSOC, false);
	foreach ($Forums as $ForumID => $Forum) {
		if (count($Forum['SpecificRules'])) {
			$DB->query("
				SELECT ThreadID
				FROM forums_specific_rules
				WHERE ForumID = $ForumID");
			$ThreadIDs = $DB->collect('ThreadID');
			$Forums[$ForumID]['SpecificRules'] = $ThreadIDs;
		}
	}
	unset($ForumID, $Forum);
	$Cache->cache_value('forums_list', $Forums, 0); //Inf cache.
	}
	return $Forums;
}

/**
 * Add a forum subscription for the provided user id
 *
 * @param $UserID
 * @param $ForumID
 */
function add_subscribed_forum($UserID, $ForumID) {
	global $DB;

	$UserID = (int)$DB->escape_str($UserID);
	$Subscriptions = get_subscribed_forums($UserID);
	if (!in_array($ForumID, $Subscriptions)) {
		$Subscriptions[] = $ForumID;
		$DB->query("INSERT INTO users_subscribed_forums (UserID, ForumID) VALUES ({$UserID}, $ForumID)");
		cache_forum_subscriptions($UserID, $Subscriptions);
	}

	// Populate the aggregate forum subscription cache
	$ForumSubscriptions = get_subscriptions_for_forum($ForumID);
	if (!in_array($UserID, $ForumSubscriptions)) {
		$ForumSubscriptions[] = $UserID;
		cache_aggregate_forum_subscriptions($ForumID, $ForumSubscriptions);
	}
}

/**
 * Remove a forum subscription from the specified user id
 *
 * @param $UserID
 * @param $ForumID
 */
function remove_subscribed_forum($UserID, $ForumID) {
	global $DB;

	$UserID = (int)$DB->escape_str($UserID);
	$Subscriptions = get_subscribed_forums($UserID);
	if (($key = array_search($ForumID, $Subscriptions)) !== false) {
		$DB->query("DELETE FROM users_subscribed_forums WHERE ForumID = $ForumID AND UserID = {$UserID}");
		unset($Subscriptions[$key]);
		cache_forum_subscriptions($UserID, $Subscriptions);
	}

	$ForumSubscriptions = get_subscriptions_for_forum($ForumID);
	if (($key = array_search($UserID, $ForumSubscriptions)) !== false) {
		unset($ForumSubscriptions[$key]);
		cache_aggregate_forum_subscriptions($ForumID, $ForumSubscriptions);
	}
}

/**
 * Get a list of forum subscriptions for the given user id
 *
 * @param $UserID
 *
 * @return array|int List of forum ids the user has subscribed to
 */
function get_subscribed_forums($UserID) {
	global $Cache, $DB;

	if (!$Subscriptions = $Cache->get_value("forum_subscriptions_{$UserID}")) {

		$UserID = (int)$DB->escape_str($UserID);
		$DB->query("SELECT * FROM users_subscribed_forums WHERE UserID = $UserID");
		$ResultSet = $DB->to_array();

		$Subscriptions = array();
		foreach ($ResultSet as $Subscription) {
			$Subscriptions[] = $Subscription['ForumID'];
		}

		cache_forum_subscriptions($UserID, $Subscriptions);

	}

	return $Subscriptions;
}

/**
 * Wrapper method to pull the global requirement on $Cache out of above methods which should
 * enable simpler unit testing
 *
 * @param $UserID
 * @param $Subscriptions
 */
function cache_forum_subscriptions($UserID, $Subscriptions) {
	global $Cache;

	$Cache->set("forum_subscriptions_{$UserID}", $Subscriptions);
}

/**
 * Returns true if the user is subscribed to the given forum id; otherwise false
 *
 * @param $UserID
 * @param $ForumID
 *
 * @return bool
 */
function check_user_forum_subscription($UserID, $ForumID) {
	$Subscriptions = get_subscribed_forums($UserID);
	if (in_array($ForumID, $Subscriptions)) {
		return true;
	} else {
		return false;
	}
}

/**
 * Returns a list of users subscribed to the given forum id
 *
 * @param $ForumID
 *
 * @return array|int List of user ids subscribed to the forum
 */
function get_subscriptions_for_forum($ForumID) {
	global $Cache, $DB;

	if (!$Subscriptions = $Cache->get_value("forum_subscriptions_forum_{$ForumID}")) {
		$DB->query("SELECT * FROM users_subscribed_forums WHERE ForumID = $ForumID");
		$ResultSet = $DB->to_array();

		$Subscriptions = array();
		foreach ($ResultSet as $Subscription) {
			$Subscriptions[] = $Subscription['UserID'];
		}
		cache_aggregate_forum_subscriptions($ForumID, $Subscriptions);
	} else {
		$Subscriptions = $Subscriptions;
	}

	return $Subscriptions;
}

/**
 * Wrapper method to pull the global requirement on $Cache out of above methods which should
 * enable simpler unit testing
 * @param $ForumID
 * @param $Subscriptions
 */
function cache_aggregate_forum_subscriptions($ForumID, $Subscriptions) {
	global $Cache;

	$Cache->set("forum_subscriptions_forum_{$ForumID}", $Subscriptions);
}

/**
 * Updates the subscriptions_user_* cache entry to add the given topic ID
 * for the specified user
 * @param $UserID
 * @param $TopicID
 */
function add_topic_subscription_for_user($UserID, $TopicID) {
	global $Cache;

	$Subscriptions = get_topic_subscriptions_for_user($UserID);
	if (!in_array($TopicID, $Subscriptions)) {
		$Subscriptions[] = $TopicID;
	}

	cache_topic_subscriptions_for_user($UserID, $Subscriptions);
}

/**
 * Returns a list of topic ids the user is subscribed to
 * @param $UserID
 *
 * @return array|int
 */
function get_topic_subscriptions_for_user($UserID) {
	global $Cache, $DB;

	if (!$Subscriptions = $Cache->get_value("subscriptions_user_$UserID")) {
		$DB->query("SELECT TopicID FROM users_subscriptions WHERE UserID = $UserID");
		$ResultSet = $DB->to_array();

		$Subscriptions = array();
		foreach ($ResultSet as $TopicID) {
			$Subscriptions[] = $TopicID;
		}

		cache_topic_subscriptions_for_user($UserID, $Subscriptions);

	}

	return $Subscriptions;
}

function cache_topic_subscriptions_for_user($UserID, $Subscriptions) {
	global $Cache;

	$Cache->set("subscriptions_user_$UserID", $Subscriptions);
}