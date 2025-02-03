<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$result = $mysql->query("SELECT `id`, `from`, `to`, `start`, `end` FROM `deadlines`")->fetch_all(MYSQLI_ASSOC);
echo $twig->render("list.deadline.html.twig", ["deadlines" => $result ?? null]);
?>