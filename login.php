<?php
session_start();
require("head.php");
require("nav.php");

function login($uid, $admin, $teacher, $active, $klasa, $sql) {
	$_SESSION['uid'] = $uid;
	$_SESSION['admin'] = $admin;
	$_SESSION['teacher'] = $teacher;
	$_SESSION['active'] = $active;
	$_SESSION['class'] = $klasa;
	$ip = get_ip();
	$query = $sql->prepare("INSERT INTO logins (user_id, ip) VALUES (?, ?)");
	$query->bind_param('is', $uid, $ip);
	$query->execute();
	//echo '<script type="text/javascript"> window.open("index.php","_self");</script>';
	header("Location: index.php");
}

if(isset($_SESSION['uid'])) {
	header("Location: index.php");
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$login = $_POST["login"];
	$plain_password = $_POST["password"];
	$password = password_hash($_POST["password"], PASSWORD_DEFAULT);
	$valid = True;
	if (!$login) {
		echo "Brak loginu!<br>";
		$valid = False;
	}
	if (!$plain_password) {
		echo "Brak hasła!<br>";
		$valid = False;
	}
	if (!$valid) {
		goto goto_exit;
	}

	$db = mysqli_connect("localhost", "root", "maciek0111", "szkola") or die(mysql_error());
	$userzy = get_users($db, $login);
	if ($userzy) {
		if (password_verify($plain_password, $userzy[0]["password"])) {
			$usr = $userzy[0];
			login($usr["id"], $usr["admin"], $usr["teacher"], $usr["active"], $usr["class"], $db);
		} else {
			echo "Złe hasło!.<br/>";
		}
		goto goto_exit;
	} else {
		echo "Nie ma takiego użytkownika!<br>";
	}
	$db->close();
}
goto_exit: ; // God please forgive me
?>
<form action="login.php" method="post">
<label> Login: <input name="login" type="text"></label><br>
<label> Haslo: <input name="password" type="password"></label><br>
<input type="submit">
</form>

<?php
require("foot.php");
?>
