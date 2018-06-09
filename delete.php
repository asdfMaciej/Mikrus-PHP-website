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
if(isset($_GET["id"])) {
        $edit_id = $_GET["id"];
        if (!ctype_digit($edit_id)) {
                go_back();
                return;
        }
        $db = mysqli_connect("localhost", "root", "maciek0111", "szkola") or die(mysql_error());
        $posts = get_text_posts($db, "", $edit_id);
        if ($posts[0]["user_id"] != $uid and $_SESSION['admin'] == 0 and $_SESSION['teacher'] == 0) {
                go_back();
                return;
        }
} else {
	go_back();
}
$query_kl = $db->prepare("DELETE FROM posts WHERE id=?");
$query_kl->bind_param('i', $edit_id);
$query_kl->execute();
$query_kl->close();
$db->close();
header("Location: posts.php");
?>

<?php
include("foot.php");
?>

