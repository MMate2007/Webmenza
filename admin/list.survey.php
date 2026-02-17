<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$active = $mysql->query("SELECT `id`, `title`, `start`, `end`, `anonim` FROM `surveys` WHERE CURRENT_DATE BETWEEN `start` AND `end` ORDER BY `title`")->fetch_all(MYSQLI_ASSOC);
$upcoming = $mysql->query("SELECT `id`, `title`, `start`, `end`, `anonim` FROM `surveys` WHERE CURRENT_DATE < `start` ORDER BY `title`")->fetch_all(MYSQLI_ASSOC);
$expired = $mysql->query("SELECT `id`, `title`, `start`, `end`, `anonim` FROM `surveys` WHERE CURRENT_DATE > `end` ORDER BY `title`")->fetch_all(MYSQLI_ASSOC);
echo $twig->render("list.survey.html.twig", ["active" => $active ?? null, "upcoming" => $upcoming ?? null, "expired" => $expired ?? null]);
?>