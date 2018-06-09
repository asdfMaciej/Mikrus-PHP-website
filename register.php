<?php
session_start();
include("head.php");
include("nav.php");
if (isset($_COOKIE["memynalesnej"])) {
	echo "Nie nadużywaj rejestracji!<br>\n";
	exit();
}
?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
<label> Login: <input name="login" type="text"></label><br>
<label> Haslo: <input name="password" type="password"></label><br>
<label> Imię: <input name="first_name" type="text"></label><br>
<label> Nazwisko: <input name="last_name" type="text"></label><br>
<label>
Klasa:
<select name="class">
<?php
$db = mysqli_connect("localhost", "root", "maciek0111", "szkola") or die(mysql_error());
$query = $db->prepare("SELECT name FROM classes;");
$query->execute();
$query->bind_result($klasa);
while($query->fetch()) {
        $escaped = htmlspecialchars($klasa);
        echo "<option value=\"$escaped\">$escaped</option>\n";
}
$query->close();
$db->close();
?>
</select>
</label><br>
<input type="submit">
</form>


<?php
if($_SERVER['REQUEST_METHOD'] != 'POST') {
	goto goto_end;
}
$login = $_POST["login"];
$plain_password = $_POST["password"];
$klasa = $_POST["class"];
$fname = $_POST["first_name"];
$lname = $_POST["last_name"];
$valid = True;
$errormsg = "";
if (!$login) {
	$errormsg .= "Brak loginu!<br>\n";
	$valid = False;
}
if (!$plain_password) {
	$errormsg .= "Brak hasła!<br>\n";
	$valid = False;
}
if (!$klasa) {
	$errormsg .= "Brak klasy!<br>\n";
	$valid = False;
}
if (!$fname) {
	$errormsg .= "Brak imienia!<br>\n";
	$valid = False;
}
if (!$lname) {
	$errormsg .= "Brak nazwiska!<br>\n";
	$valid = False;
}
echo $errormsg;
if (!$valid) {
	goto goto_end;
}

$reg = "/^[a-zA-Z0-9ĄąĆćĘęŁłŃńÓóŚśŹźŻż\-_.]{5,}$/";  // 5 or more chars alphanumeric plus - . _
$reg_pl = "/^[a-zA-ZĄąĆćĘęŁłŃńÓóŚśŹźŻż]*$/"; // polish
if(!preg_match($reg, $login) or strlen($login) > 32) {
	$errormsg .= "Login musi mieć 5 do 32 znaków i składać się z znaków alfanumerycznych lub -._<br>\n";
	$valid = False;
}
if (!preg_match($reg_pl, $fname) or strlen($fname) > 16) {
	$errormsg .= "Imię musi składać się ze znaków alfabetu i mieć do 16 liter!<br>\n";
	$valid = False;
}
if (!preg_match($reg_pl, $lname) or strlen($lname) > 24) {
	$errormsg .= "Nazwisko musi składać się ze znaków alfabetu i mieć do 24 liter!<br>\n";
	$valid = False;
}
if (strlen($plain_password) > 32) {
	$errormsg .= "Hasło musi mieć do 32 znaków!<br>\n";
	$valid = False;
}
$fname = ucwords(strtolower($fname));
$lname = ucwords(strtolower($lname));
$db = mysqli_connect("localhost", "root", "maciek0111", "szkola") or die(mysql_error());
$userzy = get_users($db, $login, $fname, $lname, $klasa);
/* foreach ($userzy as $user) {
	foreach($user as $key => $val) {
		echo "$key -> $val<br/>";
	}
	echo "--------<br/><br/>";
} */
if ($userzy) {
	$errormsg .= "Użytkownik o podanym nicku lub imieniu/nazwisku/klasie jest juz w bazie!<br/>";
	$valid = False;
}
$query_kl = $db->prepare("SELECT * FROM classes WHERE name=?");
$query_kl->bind_param('s', $klasa);
$query_kl->execute();
$stupid_way = True;
while ($query_kl->fetch()) {
	$stupid_way = False;
	break;
}
if ($stupid_way) {
	$errormsg .= "Podana klasa ". htmlspecialchars($klasa) . " nie istnieje w bazie danych!<br>";
	$valid = False;
}
$query_kl->close();
echo $errormsg;
if (!$valid) {
	goto goto_end;
}
$password = password_hash($_POST["password"], PASSWORD_DEFAULT);

$query_txt = "INSERT INTO users (username, password, class,";
$query_txt .= " first_name, last_name, active, teacher, admin, register_ip) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);";
$query = $db->prepare($query_txt);
$t = 0; // php..
$ip = get_ip();
$query->bind_param('sssssiiis', $login, $password, $klasa, $fname, $lname, $t, $t, $t, $ip);
$query->execute();
if ($query) {
        echo "Dodano użytkownika " . htmlspecialchars($login);
	setcookie("memynalesnej", "siema", time()+3600);
} else {
        echo "Query sie z..popsuło. Skontaktuj się z administratorem";
}
$query->close();
$db->close();
goto_end: ;
include("foot.php");
?>

