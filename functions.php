<?php
function get_users($conn, $login = "", $imie = "", $nazwisko = "", $klasa = "", $uid="") {
	$template = "SELECT id, username, password, class, first_name, last_name, register_date, active, teacher, admin, register_ip FROM users";
	if (!$login and !$uid and $imie) {
		$query = $conn->prepare($template . " WHERE (username=? OR (first_name=? AND last_name=? AND class=?))");
		$query->bind_param('ssss', $login, $imie, $nazwisko, $klasa);
	} elseif ($login and !$uid) {
		$query = $conn->prepare($template . " WHERE username=?;");
		$query->bind_param('s', $login);
	} elseif (!$uid) {
		$query = $conn->prepare($template);
	} else {
		$query = $conn->prepare($template . " WHERE id=?;");
		$query->bind_param('s', $uid);
	}
	$query->execute();
	$query->bind_result(
		$r_id, $r_username, $r_password, $r_class,
		$r_fname, $r_lname, $r_rdate, $r_active,
		$r_teacher, $r_admin, $r_ip
	);

	$wyniki = [];
	while ($query->fetch()) {
		$wyniki[] = [
			'id' => $r_id, 'username' => $r_username,
			'password' => $r_password, 'class' => $r_class,
			'first_name' => $r_fname, 'last_name' => $r_lname,
			'register_date' => $r_rdate, 'active' => $r_active,
			'teacher' => $r_teacher, 'admin' => $r_admin,
			'register_ip' => $r_ip
		];
	}
	$query->close();
	return $wyniki;
}

function get_text_posts($conn, $klasa="", $id="", $id_begin="") { // te funkcje mozna o wiele ladniej zrobic
						// po prostu tablica z tymi paramami s/i i dodawanie do query
					// na podstawie argumentow.... ale mi sie nie chce :v
	$template = "SELECT posts.*, users.username, users.first_name, users.last_name, users.teacher, users.admin ";
	$template .= "FROM posts INNER JOIN users ON posts.user_id = users.id WHERE posts.type=\"text\" ";
	$te = "  ORDER BY posts.id DESC limit 10";
	if (!$klasa and !$id and !$id_begin) {
		$query = $conn->prepare($template . $te);
	} elseif ($klasa and $id and !$id_begin) {
		$query = $conn->prepare($template . "AND posts.class = ? AND posts.id = ?" . $te);
		$query->bind_param('si', $klasa, $id);
	} elseif (!$id and !$id_begin) {
		$query = $conn->prepare($template . "AND posts.class = ?" . $te);
		$query->bind_param('s', $klasa);
	} elseif (!$klasa and !$id_begin) {
		$query = $conn->prepare($template . "AND posts.id = ?" . $te);
		$query->bind_param('i', $id);
	} elseif ($klasa and $id_begin) {
		$query = $conn->prepare($template . "AND posts.class = ? AND posts.id < ?" . $te);
		$query->bind_param('si', $klasa, $id_begin);
	} elseif ($id_begin) { // to jest strasznie chujowy kod, ale jest 2 w nocy i nie mam ochoty na refactor
		$query = $conn->prepare($template . "AND posts.id < ?" . $te);
		$query->bind_param('i', $id_begin);
	}  // eng: really shitty code, but not in the mood for refactoring my codebase at 2 am
	$query->execute();
	$query->bind_result(
		$r_id, $r_uid, $r_cdate, $r_textog, $r_texthtml,
		$r_type, $r_url, $r_class, $r_title, $r_nick, $r_fname, $r_lname, $r_teacher, $r_admin
	);
	$wyniki = [];
	while ($query->fetch()) {
		$wyniki[] = [
			'id' => $r_id, 'user_id' => $r_uid, 'creation_date' => $r_cdate,
			'text_original' => $r_textog, 'text_html' => $r_texthtml,
			'type' => $r_type, 'url' => $r_url, 'class' => $r_class,
			'title' => $r_title, "username" => $r_nick, "first_name" => $r_fname,
			"last_name" => $r_lname, "teacher" => $r_teacher, "admin" => $r_admin
		];
	}
	$query->close();
	return $wyniki;
}
function get_logins($conn) {
	$template = "SELECT logins.id, logins.user_id, users.username, logins.ip, logins.time ";
	$template .= "from logins inner join users on users.id = logins.user_id;";
	$query = $conn->prepare($template);
	$query->execute();
	$query->bind_result(
		$r_id, $r_uid, $r_username, $r_ip, $r_time
	);
	$wyniki = [];
	while ($query->fetch()) {
		$wyniki[] = [
			'id' => $r_id, 'user_id' => $r_uid,
			'username' => $r_username, 'ip' => $r_ip,
			'time' => $r_time
		];
	}
	$query->close();
	return $wyniki;
}
function get_classes($conn) {
	$template = "SELECT name FROM classes;";
	$query = $conn->prepare($template);
	$query->execute();
	$query->bind_result($r_class);
	$klasy = [];
	while ($query->fetch()) {
		$klasy[] = $r_class;
	}
	$query->close();
	return $klasy;
}
function get_ip() {
	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR']; // security risk, but proxy on mikr.us
        } else {
                $ip = $_SERVER['REMOTE_ADDR'];
        }
	return $ip;
}

function validate_fname($fname) {
$reg_pl = "/^[a-zA-Z▒^▒▒^▒▒^▒▒^▒▒^▒▒^▒▒^▒▒^▒▒^▒▒^▒▒^▒ó▒^▒▒^▒ŹźŻż]*$/"; // polish
$valid = True;
if (!preg_match($reg_pl, $fname) or strlen($fname) > 16) {
        $valid = False;
}
return $valid;
}

function validate_lname($lname) {
$reg_pl = "/^[a-zA-Z▒^▒▒^▒▒^▒▒^▒▒^▒▒^▒▒^▒▒^▒▒^▒▒^▒▒^▒ó▒^▒▒^▒ŹźŻż]*$/"; // polish
$valid = True;
if (!preg_match($reg_pl, $lname) or strlen($lname) > 24) {
        $valid = False;
}
return $valid;
}

function user_type($session) {
	$types = [];
	if (!isset($session['uid'])) {
		return ["guest"];
	}
	if ($session['active'] == 0) {
		return ["unactive"];
	}
	if ($session['admin']) {
		$types[] = "admin";
	}
	if ($session['teacher']) {
		$types[] = "teacher";
	}
	$types[] = "active";
	return $types;
}
function split($string) {
	$array = preg_split("/\r\n|\n|\r/", $string);
	return $array;
}
function go_back() {
	header("location:javascript://history.go(-1)");
}
function markdown($string) {
	// it's not exactly markdown
	// but it should do its job (prove me wrong)
	// ~ Maciej Kaszkowiak, 27.05.2018
	$string = htmlspecialchars($string);
	$lines = split($string);
	if (count($lines) > 80) {
		return 0;
	}
	$html = "";
	foreach ($lines as $line) {
		$h2 = 0;
		$newline = 1;
		if (substr($line, 0, 2) == "# ") {
			$html .= "<h2>";
			$h2 = 1;
			$newline = 0;
			$line = substr($line, 2);
		}
		if (substr($line, 0, 3) == "---") {
			$html .= "<hr>";
			$newline = 0;
			$line = substr($line, 3);
		}
		$html .= $line;
		if ($h2) {
			$html .= "</h2>";
		}
		if ($newline) {
			$html .=  "<br>";
		}
		$html .= "\n";
	}
	$bold_lines = explode("**", $html);
	if (count($bold_lines) < 3) {
		return $html;
	}
	$html = "";
	$opened = 0;
	foreach ($bold_lines as $line) {
		$html .= $line;
		if (!$opened) {
			$html .= "<b>";
			$opened = 1;
		} else {
			$html .= "</b>";
			$opened = 0;
		}
	}
	if ($opened) {
		$html = substr($html, 0, -3);
	}
	return $html;
}
?>

