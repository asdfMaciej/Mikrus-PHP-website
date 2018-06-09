<?php
session_start();
include("head.php");
include("nav.php");
if(!isset($_SESSION['uid'])) {
	header("Location: index.php");
	return;
}
if ($_SESSION['active'] == 0) {
	header("Location: index.php");
	return;
}
if (!$_SESSION['admin']) {
	header("Location: index.php");
	return;
}

$db = mysqli_connect("localhost", "root", "maciek0111", "szkola") or die(mysql_error());
$logi = get_logins($db);
$db->close();
?>

<table style="width: 100%">
<tr>
	<th>ID</th>
	<th>ID uzytkownika</th>
	<th>Nick</th>
	<th>IP</th>
	<th>Czas</th>
</tr>
<?php foreach ($logi as $l) {
	$id = $l["id"];
	$uid = $l["user_id"];
	$nick = $l["username"];
	$ip = $l["ip"];
	$time = $l["time"];
?>
<tr>
	<th><?=$id?></th>
	<th><?=$uid?></th>
	<th><?=$nick?></th>
	<th><?=$ip?></th>
	<th><?=$time?></th>
</tr>
<?php } // end of foreach ?>
</table>
<h1>
<a href="admin.php">WROC DO PANELU ADMINA</a> 
</h1>
<?php
include("foot.php");
?>
