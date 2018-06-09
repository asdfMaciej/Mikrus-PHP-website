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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$actions = [
		"activate" => "UPDATE users SET active=1 WHERE ",
		"deactivate" => "UPDATE users SET active=0 WHERE ",
		"teach" => "UPDATE users SET teacher=1 WHERE ",
		"deteach" => "UPDATE users SET teacher=0 WHERE ",
		"delete" => "DELETE FROM users WHERE ",
		"rename" => ""
	];
	$actions_special = ["rename"];
	$act = $_POST["action"];
	if (!array_key_exists($act, $actions)) {
		echo "Zly post request!<br>\n";
		goto goto_exit;
	}
	if (!isset($_POST["user_id"]) or empty($_POST["user_id"])) {
		echo "Nie wybrano zadnych osob.<br>\n";
		goto goto_exit;
	}
	$query_gen = $actions[$act];
	if ($act == "rename") {
		if ($act == "rename") {
			$valid = validate_fname($_POST["fname"]) and validate_lname($_POST["lname"]);
			if (!$valid) {
				echo "Imie lub nazwisko niepoprawne.<br>\n";
				goto goto_exit;
			}
			$fname = $_POST["fname"];
			$lname = $_POST["lname"];
			$query_gen = "UPDATE users SET first_name=\"$fname\", last_name=\"$lname\" WHERE ";

		}
	}
	$first = 1;
	foreach ($_POST["user_id"] as $uid) {
		if ($first==1) {
			$first = 0;
		} else {
			$query_gen .= " OR ";
		}
		if (!ctype_digit($uid)) {
			echo "Chcesz bana?<br>\n";
			goto goto_exit;
		}
		$query_gen .= "id = " . $uid;
	}
	$query_gen .= ";";
	$query = $db->prepare($query_gen);
	$query->execute();
	echo "Wykonano. Dotknieto " . $query->affected_rows . " użytkowników.<br>\n";
	$query->close();
}
goto_exit: ; // Boze wybacz
$userzy = get_users($db);
$db->close();
?>

<form method="post" action="admin.php">
<table style="width: 100%">
<tr>
	<th>ID</th>
	<th>Nick</th>
	<th>Klasa</th>
	<th>Imię</th>
	<th>Nazwisko</th>
	<th>IP rejestracji</th>
	<th>Aktywny</th>
	<th>Nauczyciel</th>
	<th>Admin</th>
	<th>Zaznacz użytkownika</th>
</tr>
<?php foreach ($userzy as $user) { 
	$uid = $user["id"];
	$nick = $user["username"];
	$klasa = $user["class"];
	$imie = $user["first_name"];
	$nazwisko = $user["last_name"];
	$ip = $user["register_ip"];
	$aktywny = $user["active"] == 1 ? "checked" : "";
	$nauczyciel = $user["teacher"] == 1 ? "checked" : "";
	$admin = $user["admin"] == 1 ? "checked" : "";
?>
<tr>
	<th><?=$uid?></th>
	<th><?=$nick?></th>
	<th><?=$klasa?></th>
	<th><?=$imie?></th>
	<th><?=$nazwisko?></th>
	<th><?=$ip?></th>
	<th><input type="checkbox" onclick="return false;" <?=$aktywny?>></th>
	<th><input type="checkbox" onclick="return false;" <?=$nauczyciel?>></th>
	<th><input type="checkbox" onclick="return false;" <?=$admin?>></th>
	<th><input type="checkbox" name="user_id[]" value="<?=$uid?>"/></th>
</tr>
<?php } // end of foreach ?>
</table>
<div>
	<label>Imie: <input type="text" name="fname"></label><br>
	<label>Nazwisko: <input type="text" name="lname"></label><br>
	<button type="submit" name="action" value="activate">Aktywuj konta</button>
	<button type="submit" name="action" value="deactivate">Dezaktywuj konta</button>
	<button type="submit" name="action" value="teach">Nadaj nauczyciela</button>
	<button type="submit" name="action" value="deteach">Odbierz nauczyciela</button>
	<button type="submit" name="action" value="delete">USUŃ KONTA</button>
	<button type="submit" name="action" value="rename">Zmien imie/nazwisko</button>
</div>
</form>

<h1>
<a href="logi.php">PRZEJDZ DO LOGOW</a>
</h1>
<?php
include("foot.php");
?>
