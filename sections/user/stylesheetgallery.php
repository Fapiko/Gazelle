<?

if (!isset($_GET['name'])) {
	if (!isset($Err)) {
		error(404);
	}
} else {
	$Name = false;
	foreach ($Stylesheets as $Stylesheet) {
		if ($Stylesheet["Name"] === $_GET['name'])
			$Name = $_GET['name'];
	}
	if (!$Name) {
		error(404);
	}
	if (isset($_GET['format']) && $_GET['format'] === "data") {
		global $Cache;
		$ImageData = $Cache->get_value("cssgallery_".$Name);
		if (!empty($ImageData)) {
			echo json_encode(array('data' => $ImageData, 'status' => "0"));
			die();
		} else {
			echo json_encode(array('status' => "-1"));
			die();
		}
	} else {
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" style="overflow: hidden !important; margin: 0 !important; padding: 0 !important;">
	<head>
		<title>Stylesheet Gallery</title>
		<meta http-equiv="X-UA-Compatible" content="chrome=1; IE=edge" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link href="<? echo STATIC_SERVER; ?>styles/global.css?v=<?=filemtime(STATIC_SERVER.'styles/global.css')?>" rel="stylesheet" type="text/css" />
		<link href="<? echo STATIC_SERVER; ?>styles/<?= $Name ?>/style.css?v=<?=filemtime(STATIC_SERVER.'styles/'.$Name.'/style.css')?>" title="<?= $Name ?>" rel="stylesheet" type="text/css" media="screen" />
<?		if (isset($_GET['save']) && $_GET['save'] === 'true' && check_perms('admin_clear_cache')) { ?>
		<script src="<? echo STATIC_SERVER; ?>functions/jquery.js?v=<?=filemtime(STATIC_SERVER.'functions/jquery.js')?>"></script>
		<script src="<? echo STATIC_SERVER; ?>functions/stylesheetgallery.js?v=<?=filemtime(STATIC_SERVER.'functions/stylesheetgallery.js')?>"></script>
<?		} ?>
	</head>
	<body id="user" style="overflow: hidden !important; margin: 0 !important; padding: 0 !important; position: absolute !important;" stylesheet="<?= $Name ?>">
		<div id="wrapper">
			<h1 class="hidden">Gazelle</h1>
			<div id="header">
				<div id="logo"><a href="#"></a></div>
				<div id="userinfo">
					<ul id="userinfo_username">
						<li id="nav_userinfo"><a href="#" class="username">Gazelle</a></li>
						<li id="nav_useredit" class="brackets"><a href="#">Edit</a></li>
						<li id="nav_logout" class="brackets"><a href="#">Logout</a></li>
					</ul>
					<ul id="userinfo_major">
						<li id="nav_upload" class="brackets"><a href="#">Upload</a></li>
						<li id="nav_invite" class="brackets"><a href="#">Invite (6)</a></li>
						<li id="nav_donate" class="brackets"><a href="#">Donate</a></li>

					</ul>
					<ul id="userinfo_stats">
						<li id="stats_seeding"><a href="#">Up</a>: <span class="stat" title="300.00000 MB">300.00 MB</span></li>
						<li id="stats_leeching"><a href="#">Down</a>: <span class="stat" title="10.00000 B">0.00 B</span></li>
						<li id="stats_ratio">Ratio: <span class="stat"><span class="r99" title="30">30</span></span></li>
						<li id="stats_required"><a href="#">Required</a>: <span class="stat" title="0.00000">0.00</span></li>
					</ul>

					<ul id="userinfo_minor">
						<li id="nav_inbox"><a onmousedown="Stats('inbox');" href="#">Inbox</a></li>
						<li id="nav_staffinbox"><a onmousedown="Stats('staffpm');" href="#">Staff Inbox</a></li>
						<li id="nav_uploaded"><a onmousedown="Stats('uploads');" href="#">Uploads</a></li>
						<li id="nav_bookmarks"><a onmousedown="Stats('bookmarks');" href="#">Bookmarks</a></li>
						<li id="nav_notifications" ><a onmousedown="Stats('notifications');" href="#">Notifications</a></li>
						<li id="nav_subscriptions"><a onmousedown="Stats('subscriptions');" href="#">Subscriptions</a></li>
						<li id="nav_comments"><a onmousedown="Stats('comments');" href="#">Comments</a></li>
						<li id="nav_friends"><a onmousedown="Stats('friends');" href="#">Friends</a></li>
					</ul>
				</div>
				<div id="menu">
					<h4 class="hidden">Site Menu</h4>
					<ul>
						<li id="nav_index"><a href="#">Home</a></li>
						<li id="nav_torrents"><a href="#">Torrents</a></li>
						<li id="nav_collages"><a href="#">Collages</a></li>
						<li id="nav_requests"><a href="#">Requests</a></li>
						<li id="nav_forums"><a href="#">Forums</a></li>
						<li id="nav_irc"><a href="#">IRC</a></li>
						<li id="nav_top10"><a href="#">Top 10</a></li>
						<li id="nav_rules"><a href="#">Rules</a></li>
						<li id="nav_wiki"><a href="#">Wiki</a></li>
						<li id="nav_staff"><a href="#">Staff</a></li>
					</ul>
				</div>
				<div id="alerts">
					<div class="alertbar blend"><a href="#">New Announcement!</a></div>
				</div>
				<div id="searchbars">
					<ul>
						<li id="searchbar_torrents">
							<span class="hidden">Torrents: </span>
							<form class="search_form" name="torrents" action="" method="get">
								<input
									id="torrentssearch"
									accesskey="t"
									spellcheck="false"
									onfocus="if (this.value == 'Torrents') this.value='';"
									onblur="if (this.value == '') this.value='Torrents';"
									value="Torrents" type="text" name="searchstr" size="17"
									/>
							</form>
						</li>
						<li id="searchbar_artists">
							<span class="hidden">Artist: </span>
							<form class="search_form" name="artists" action="" method="get">
								<script type="text/javascript" src="static/functions/autocomplete.js?v=1362029969"></script>
								<input id="artistsearch" value="Artists" type="text" name="artistname" size="17" />
								<ul id="artistcomplete" style="visibility: hidden;"><li/></ul>
							</form>
						</li>
						<li id="searchbar_requests">
							<span class="hidden">Requests: </span>
							<form class="search_form" name="requests" action="" method="get">
								<input id="requestssearch" value="Requests" type="text" name="search" size="17" />
							</form>
						</li>
						<li id="searchbar_forums">
							<span class="hidden">Forums: </span>
							<form class="search_form" name="forums" action="" method="get">
								<input value="search" type="hidden" name="action" />
								<input id="forumssearch" value="Forums" type="text" name="search" size="17" />
							</form>
						</li>
						<li id="searchbar_log">
							<span class="hidden">Log: </span>
							<form class="search_form" name="log" action="" method="get">
								<input id="logsearch" value="Log" type="text" name="search" size="17" />
							</form>
						</li>
						<li id="searchbar_users">
							<span class="hidden">Users: </span>
							<form class="search_form" name="users" action="" method="get">
								<input type="hidden" name="action" value="search" />
								<input id="userssearch" value="Users" type="text" name="search" size="20" />
							</form>
						</li>
					</ul>
				</div>
			</div>
			<div id="content">
				<div class="thin">
					<h2>Forums</h2>
					<div class="forum_list">
						<h3>Site</h3>
						<table class="forum_index">
							<tbody>
								<tr class="colhead">
									<td style="width: 2%;"></td>
									<td style="width: 25%;">Forum</td>
									<td>Last post</td>
									<td style="width: 7%;">Topics</td>
									<td style="width: 7%;">Posts</td>
								</tr>
								<tr class="rowb">
									<td title="Unread" class="unread"></td>
									<td>
										<h4 class="min_padding">
											<a href="#">Announcements</a>
										</h4>
									</td>
									<td>
										<span class="last_topic" style="float: left;">
											<a title="New Site Announcements" href="#">New Site Announcements</a>
										</span>
										<span title="Jump to last read" class="last_read" style="float: left;">
											<a href="#"></a>
										</span>
										<span class="last_poster" style="float: right;">by <a href="#">Rippy</a> <span title="Aug 14 1992, 18:35" class="time">Just now</span></span>
									</td>
									<td>385</td>
									<td>95,197</td>
								</tr>
								<tr class="rowa">
									<td title="Read" class="read"></td>
									<td>
										<h4 class="min_padding">
											<a href="#"><?=SITE_NAME?></a>
										</h4>
									</td>
									<td>
										<span class="last_topic" style="float: left;">
											<a title="Dear Mortals, you have violated the rule..." href="#">Dear Mortals, you have violated the rule...</a>
										</span>
										<span class="last_poster" style="float: right;">by <a href="#">Drone</a> <span title="Sep 9 1992, 10:55" class="time">3 mins ago</span></span>
									</td>
									<td>2,624</td>
									<td>110,432</td>
								</tr>
							</tbody>
						</table>
						<h3>Community</h3>
						<table class="forum_index">
							<tbody><tr class="colhead">
									<td style="width: 2%;"></td>
									<td style="width: 25%;">Forum</td>
									<td>Last post</td>
									<td style="width: 7%;">Topics</td>
									<td style="width: 7%;">Posts</td>
								</tr>
								<tr class="rowb">
									<td title="Unread" class="unread"></td>
									<td>
										<h4 class="min_padding">
											<a href="#">The Lounge</a>
										</h4>
									</td>
									<td>
										<span class="last_topic" style="float: left;">
											<a title="Last stand against Drone?" href="#">Last stand against Drone?</a>
										</span>
										<span title="Jump to last read" class="last_read" style="float: left;">
											<a href="#"></a>
										</span>
										<span class="last_poster" style="float: right;">by <a href="#">Ajax</a> <span class="time">Just now</span></span>
									</td>
									<td>37,531</td>
									<td>1,545,089</td>
								</tr>
								<tr class="rowa">
									<td title="Unread" class="unread"></td>
									<td>
										<h4 class="min_padding">
											<a href="#">The Lounge +1</a>
										</h4>
									</td>
									<td>
										<span class="last_topic" style="float: left;">
											<a title="No fun allowed" href="#">No fun allowed</a>
										</span>
										<span class="last_poster" style="float: right;">by <a href="#">Drone</a> <span class="time">10 mins ago</span></span>
									</td>
									<td>424</td>
									<td>490,163</td>
								</tr>
								<tr class="rowb">
									<td title="Read" class="read"></td>
									<td>
										<h4 class="min_padding">
											<a href="#">The Library</a>
										</h4>
									</td>
									<td>
										<span class="last_topic" style="float: left;">
											<a title="List of forbidden literature" href="#">List of forbidden literature</a>
										</span>
										<span class="last_poster" style="float: right;">by <a href="#">Drone</a> <span class="time">7 hours ago</span></span>
									</td>
									<td>424</td>
									<td>490,163</td>
								</tr>
								<tr class="rowa">
									<td title="Read" class="read"></td>
									<td>
										<h4 class="min_padding">
											<a href="#">Concerts, Events & Meets</a>
										</h4>
									</td>
									<td>
										<span class="last_topic" style="float: left;">
											<a title="[Region] The Void" href="#">[Region] The Void</a>
										</span>
										<span class="last_poster" style="float: right;">by <a href="#">Drone</a> <span class="time">25 mins ago</span></span>
									</td>
									<td>305</td>
									<td>15,231</td>
								</tr>
								<tr class="rowb">
									<td title="Unread" class="unread"></td>
									<td>
										<h4 class="min_padding">
											<a href="#">Technology</a>
										</h4>
									</td>
									<td>
										<span class="last_topic" style="float: left;">
											<a title="How did Drone take over the site?" href="#">How did Drone take over the site?</a>
										</span>
										<span title="Jump to last read" class="last_read" style="float: left;">
											<a href="#"></a>
										</span>
										<span class="last_poster" style="float: right;">by <a href="#">Etheryte</a> <span class="time">5 mins ago</span></span>
									</td>
									<td>25,031</td>
									<td>386,278</td>
								</tr>
							</tbody>
						</table>
						<h3>Music</h3>
						<table class="forum_index">
							<tbody>
								<tr class="colhead">
									<td style="width: 2%;"></td>
									<td style="width: 25%;">Forum</td>
									<td>Last post</td>
									<td style="width: 7%;">Topics</td>
									<td style="width: 7%;">Posts</td>
								</tr>
								<tr class="rowb">
									<td title="Unread" class="unread"></td>
									<td>
										<h4 class="min_padding">
											<a href="#">Music</a>
										</h4>
									</td>
									<td>
										<span class="last_topic" style="float: left;">
											<a title="Where did all the non-drone threads go?" href="#">Where did all the non-drone threads go?</a>
										</span>
										<span title="Jump to last read" class="last_read" style="float: left;">
											<a href="#"></a>
										</span>
										<span class="last_poster" style="float: right;">by <a href="#">Ananke</a> <span class="time">1 min ago</span></span>
									</td>
									<td>22,564</td>
									<td>608,253</td>
								</tr>
								<tr class="rowa">
									<td title="Unead" class="unread"></td>
									<td>
										<h4 class="min_padding">
											<a href="#">Vanity House</a>
										</h4>
									</td>
									<td>
										<span class="last_topic" style="float: left;">
											<a title="Drone - Drone [EP] (drone, noise)" href="#">Drone - Drone [EP] (drone, noise)</a>
										</span>
										<span class="last_poster" style="float: right;">by <a href="#">Drone</a> <span class="time">Just now</span></span>
									</td>
									<td>3,948</td>
									<td>24,269</td>
								</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
	</body>
</html>
<?
	}
}
