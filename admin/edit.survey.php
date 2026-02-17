<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$stmt = $mysql->prepare("SELECT `title`, `description`, `questions`, CASE WHEN `start` > CURRENT_DATE THEN TRUE ELSE FALSE END AS `editable` FROM `surveys` WHERE `id` = ?");
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
$result = $stmt->get_result()->fetch_row();
echo $twig->render("edit.survey.html.twig", ["title" => $result[0], "questions" => $result[2], "editable" => $result[3], "description" => $result[1]]);
?>