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
$klasa = $_SESSION['class'];
$db = mysqli_connect("localhost", "root", "maciek0111", "szkola") or die(mysql_error());
$fr = "";
if (isset($_GET['from'])) {
	if (ctype_digit($_GET['from'])) {
		$fr = $_GET['from'];
	}
}
if ($_SESSION['admin'] == 1) {
	$posty = get_text_posts($db, "", "", $fr);
} else {
	$posty = get_text_posts($db, $klasa, "", $fr);
}
function navigation($fr, $posty) { // don't judge ... pls
echo '<div class="next_posts" style="text-align: center">';
if ($fr) {
        echo '<a style="float: left; color: #131516;" href="posts.php">';
	echo "&#x2190 Poczatek</a>";
}
echo '<a style="color: #131516;" href="add.php">Dodaj post</a> ';
if (count($posty) == 10) {
	$lastid = end($posty)['id'];
	echo '<a style="float: right; color: #131516;" href="posts.php?from=' . $lastid . '">';
	echo "Nastepna strona &#x2192</a>";
}
echo '</div>';
}
navigation($fr, $posty);
?>
<section class="">
<?php
foreach ($posty as $p) {
$data = $p['creation_date'];
$tekst = $p['text_html'];
$tytul = $p['title'];
$klasa = $p['class'];
$autor = $p['first_name'] . " " . $p['last_name'];
$nauczyciel = $p['teacher'];
$admin = $p['admin'];
$id = $p['id'];
?>
<a class="post" href="post.php?id=<?=$id?>">
<div class="post">
<div class="post_icon">
	<?php
if($nauczyciel) {
	echo "&#x265B";
} elseif ($admin) {
	echo "&#x26A0";
}
	?>
</div>
<div class="post_rest">
	<div class="post_title"><?=$tytul?></div>
	<div class="post_date"><b><?=$autor?></b> - <?=$data?> (<?=$klasa?>)<br></div>
	</div>
</div>
</a>
<?php
} // end of foreach
?>
</section>
<?php
if (count($posty) == 10) {
	navigation($fr, $posty, True);
}
?>
<?php
include("foot_nocontent.php");
?>
