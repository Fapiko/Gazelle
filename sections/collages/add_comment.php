<?
authorize();

if (empty($_POST['collageid']) || !is_number($_POST['collageid']) || $_POST['body'] === '' || !isset($_POST['body'])) {
	error(0);
}
$CollageID = $_POST['collageid'];

if ($LoggedUser['DisablePosting']) {
	error('Your posting privileges have been removed'); // Should this be logged?
}

$DB->query("INSERT INTO collages_comments
	(CollageID, Body, UserID, Time)
	VALUES
	('$CollageID', '".db_string($_POST['body'])."', '$LoggedUser[ID]', '".sqltime()."')");

$Cache->delete_value('collage_'.$CollageID.'_catalogue_0');
$Cache->delete_value('collage_'.$CollageID);
header('Location: collages.php?id='.$CollageID);

?>
