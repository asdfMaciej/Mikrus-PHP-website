<?php
session_start();
include("head.php");
include("nav.php");
if(!isset($_SESSION['uid'])) {?>
Maciej Kaszkowiak. github.com/asdfMaciej<br>
<a href="login.php">Zaloguj sie.</a><br>
<a href="register.php">Zarejestruj sie.</a><br>
<?php
goto goto_end;
}
?>
<div style="font-size: 125%; display: flex; align-items: center; flex-direction: 
column;">
<h1 style="font-size: 300%;">Zalogowany!</h1><br>
<img src="memy.jpg" style="max-width: 384px; width: 50%; display: none;">
<?php
if ($_SESSION['active'] == 0) {
	echo "Twoje konto jeszcze nie jest aktywne. ";
	echo "Skontaktuj sie z administratorem lub zaloguj sie ponownie.";
	echo "<br>\n";
	goto goto_end;
}
if ($_SESSION['admin']) {
	echo "<a href=\"admin.php\">Przejdz do panelu administracyjnego</a>\n";
}
if ($_SESSION['teacher']) {
	echo "Witam nauczyciela!<br>\n";
}
?>
<a href="posts.php">Przejdz do postow</a>
<a href="add.php">Dodaj nowy post.</a><br>

<?php
goto_end: ;
?>
</div>
<?php
include("foot.php");
?>
