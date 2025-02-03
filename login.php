<?php
include("config.php");

if (isset($_POST["name"])) {
    $mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
    $mysql->query("SET NAMES utf8");
    $stmt = $mysql->prepare("SELECT `users`.`id`, `groups`.`name`, `password`, `users`.`name`, `registered` FROM `users` LEFT JOIN `groups` ON `users`.`groupId` = `groups`.`id` WHERE `users`.`name` = ?");
    $stmt->execute([$_POST["name"]]);
    $result = $stmt->get_result();
    while ($row = $result->fetch_row()) {
        if (password_verify($_POST["password"], $row[2])) {
            session_regenerate_id();
            if ($row[1] === "_admin") $_SESSION["admin"] = 2; else if ($row[1] === "_manager") $_SESSION["admin"] = 1;
            $_SESSION["userId"] = $row[0];
            $_SESSION["name"] = $row[3];
            if ($row[4] == false) {
                Message::addMessage("Első belépésnél a jelszóváltoztatás köztelező! Kérjük, hogy változtassa meg jelszavát!", MessageType::warning, false);
                $_SESSION["registered"] = false;
                header("Location: modify.password.php");
                exit;
            }
            if (isset($_SESSION["admin"])) {
                header("Location: admin/index.php");
                exit;
            }
            header("Location: home.php");
            exit;
        } else {
            Message::addMessage("Helytelen jelszó!", MessageType::danger);
        }
    }
    if ($result->num_rows == 0) {
        Message::addMessage("A megadott felhasználó nem létezikl!", MessageType::danger);
    }

}

if (!isset($_SESSION["userId"])) {
echo $twig->render("adminlogin.html.twig");
} else if (isset($_SESSION["admin"])) {
    header("Location: admin/index.php");
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>