<?php
session_start();
include("head.php");
include("nav.php");
if(!isset($_SESSION['uid'])) {
        header("Location: index.php");
}
if ($_SESSION['active'] == 0) {
        header("Location: index.php");
}
$uid = $_SESSION['uid'];
$edit = False;
if(isset($_GET["edit_id"])) {
        $edit_id = $_GET["edit_id"];
        if (!ctype_digit($edit_id)) {
                echo "nt";
                return;
        }
        $db = mysqli_connect("localhost", "root", "maciek0111", "szkola") or die(mysql_error());
        $posts = get_text_posts($db, "", $edit_id);
        if ($posts[0]["user_id"] != $uid and $_SESSION['admin'] == 0) {
                echo "nt";
                return;
        }
        $edit = True;
        $edit_post = $posts[0];
	$db->close();
}
?>

<div id="add_form">
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" method="post">
<label> Tytuł: <input name="title" style="width: 100%" type="text" <?php if ($edit) {echo "value=\"" . 
$edit_post["title"] . "\"";}?>></label><br><br>
<label> Post: <textarea name="post" rows="16" cols="50" style="width: 100%">
<?php
if ($edit) {
	echo $edit_post["text_original"];
} else { ?>
	Wprowadź zawartość posta...
<?php }?>
</textarea></label><br>
<label>
Klasa:<br>
<select name="class">
<?php
if ($_SESSION['admin'] == 0) {
	echo "<option value=\"" . $_SESSION['class'] . "\">" . $_SESSION['class'] . "</option>\n";
	goto goto_0;
}
$db = mysqli_connect("localhost", "root", "maciek0111", "szkola") or die(mysql_error());
$query = $db->prepare("SELECT name FROM classes;");
$query->execute();
$query->bind_result($klasa);
while($query->fetch()) {
        $escaped = htmlspecialchars($klasa);
        echo "<option value=\"$escaped\"";
	if ($edit) {
		if ($klasa == $edit_post["class"]) {
			echo " selected ";
		}
	}
	echo ">$escaped</option>\n";
}
$query->close();
$db->close();
goto_0: ;
?>
</select>
</label><br>
<?php
if ($edit) {
	echo '<input type="hidden" name="edit" value="' . $edit_id . '">' . "\n";
}
?>
<br>
<input type="submit">

</form>
</div><br><br>
<b>Formatowanie tekstu</b> - poradnik:<br>
Uzyj **podwojnych gwiazdek**, aby <b>pogrubic</b> element;<br>
Uzyj --- trzech kresek, zeby wstawic linie horyzontalna;<br>
# Uzyj hasha i przerwy w celu wstawienia naglowka - przyklad:<br><br>
Bla bla tekst by **John Doe**<br>
# Paragraf 1<br>
---<br>
Pisze o czyms **waznym**<br>
<?php
if($_SERVER['REQUEST_METHOD'] != 'POST') {
	goto goto_end;
}
$post = $_POST["post"];
$klasa = $_POST["class"];
$title = $_POST["title"];
$valid = True;
$errormsg = "";
if (!$post) {
	$errormsg .= "Post jest pusty!<br>\n";
	$valid = False;
}
if (!$klasa) {
	$errormsg .= "Brak klasy!<br>\n";
	$valid = False;
}
if (!$title) {
	$errormsg .= "Brak tytułu!<br>\n";
	$valid = False;
}
echo $errormsg;
if (!$valid) {
	goto goto_end;
}

$post_size = strlen($post);
if ($post_size < 8 or $post_size > 6000) {
	$errormsg .= "Post musi mieć między 8 a 6000 znaków!<br>\n";
	$valid = False;
}
if (strlen($title) < 8 or strlen($title) > 180) {
	$errormsg .= "Tytuł musi mieć między 8 a 180 znaków!<br>\n";
	$valid = False;
}
$db = mysqli_connect("localhost", "root", "maciek0111", "szkola") or die(mysql_error());
$edit = False;
if (isset($_POST["edit"])) {
	$edit = True;
	$edit_id = $_POST["edit"];
	if (!ctype_digit($edit_id)) {
                $errormsg .= "nt";
                $valid = False;
        }
        $posts = get_text_posts($db, "", $edit_id);
        if ($posts[0]["user_id"] != $uid and $_SESSION['admin'] == 0) {
                $errormsg .= "nt";
                $valid = False;
        }
}

$title = htmlspecialchars($title);
$db = mysqli_connect("localhost", "root", "maciek0111", "szkola") or die(mysql_error());
$query_kl = $db->prepare("SELECT * FROM classes WHERE name=?");
$query_kl->bind_param('s', $klasa);
$query_kl->execute();
$stupid_way = True;
while ($query_kl->fetch()) {
        $stupid_way = False;
        break;
}
if ($stupid_way) {
        $errormsg .= "Podana klasa " . htmlspecialchars($klasa) . " nie istnieje w bazie danych!<br>";
        $valid = False;
}
$query_kl->close();

$html_markdown = markdown($post);
if ($html_markdown === 0) {
	$errormsg .= "Za długi post pod względem ilości linijek!<br>";
	$valid = False;
}
echo $errormsg;
if (!$valid) {
	$db->close();
        goto goto_end;
}

if ($edit) {
	$query_txt = "UPDATE posts SET text_original = ?, text_html = ?, class = ?, title = ? ";
	$query_txt .= "WHERE id = ?";
	$query = $db->prepare($query_txt);
	$query->bind_param("ssssi", $post, $html_markdown, $klasa, $title, $edit_id);
} else {
	$query_txt = "INSERT INTO posts (user_id, text_original, text_html, ";
	$query_txt .= "type, url, class, title) VALUES (?, ?, ?, ?, ?, ?, ?)";
	$query = $db->prepare($query_txt);
	$_e = ""; // i love php!
	$_t = "text";  // i totally do!
	$query->bind_param("issssss", $uid, $post, $html_markdown, $_t, $_e, $klasa, $title);
}
$query->execute();

if ($query) {
	if ($edit) {
		echo "Zedytowane post! <a href=\"post.php?id=$edit_id\">Przejdź do posta.</a>";
	} else {
		echo "Dodano post! <a href=\"posts.php\">Przejdź do postów.</a>";
	}
}
$query->close();
$db->close();
goto_end: ;
?>

<?php
include("foot.php");
?>

