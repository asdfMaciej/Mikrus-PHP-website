<body>
<div id="header">
<a href="index.php" style="color: #F0F0F0;"><b>Kaszkowiak</b></a>
<div style="float: right; text-align: right; margin-right: 3%; font-size: 35%;">
<?php
require("functions.php");
if(!isset($_SESSION['uid'])) {
echo "Nie jestes zalogowany.\n";
} else {
$db = mysqli_connect("localhost", "root", "maciek0111", "szkola") or die(mysql_error());
$userzy = get_users($db, "", "", "", "", $_SESSION['uid']);
$nazw = $userzy[0]["first_name"] . " " . $userzy[0]["last_name"];
echo $nazw . "<br>";
echo "<a href=\"logout.php\" style=\"color: #F0F0F0\">Wyloguj sie.</a><br>\n";
}
?>
</div>
</div>
