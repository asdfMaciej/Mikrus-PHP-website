<?php
session_start();
include("head.php");
include("nav_nocontent.php");
if(!isset($_SESSION['uid'])) {
	header("Location: index.php");
}
if ($_SESSION['active'] == 0) {
	header("Location: index.php");
}
if (!isset($_GET['id'])) {
	header("Location: posts.php");
}
$id = $_GET['id'];
$klasa = $_SESSION['class'];
$db = mysqli_connect("localhost", "root", "maciek0111", "szkola") or die(mysql_error());
if ($_SESSION['admin'] == 1) {
	$posty = get_text_posts($db, "", $id);
} else {
	$posty = get_text_posts($db, $klasa, $id);
}
if ($posty == []) {
	header("Location: posts.php");
}
$p = $posty[0];
$data = $p['creation_date'];
$tekst = $p['text_html'];
$tytul = $p['title'];
$klasa = $p['class'];
$autor = $p['first_name'] . " " . $p['last_name'];
$nauczyciel = $p['teacher'];
$id = $p['id'];
?>
<div class="post noborder">
<a class="post" href="posts.php">
<div class="post_rest">
<div class="post_title"><?=$tytul?></div>
<div class="post_date"><b><?=$autor?></b> - <?=$data?> (<?=$klasa?>)<br></div>
</div>
</a>
<?php
$condition = $_SESSION['admin'] == 1 or $p['user_id'] == $_SESSION['uid'];
if ($condition or $_SESSION['teacher'] == 1) { ?>
<div class="post_iconr">
<span>
<a style="color: #131516; float: left; text-decoration: none;" href="delete.php?id=<?=$id?>"><?php echo "&#x2620"; ?></a>
<?php if ($condition) { ?>
<a style="color: #131516; float:right; text-decoration: none;" href="add.php?edit_id=<?=$id?>"><?php echo "&#x270e"; ?></a>
<?php } ?>
</span>
</div>
<?php } ?>
</div>
<div class="post_text">
<?=$tekst?>
</div>
<?php
include("foot_nocontent.php");
?>
