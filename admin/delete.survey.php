<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$stmt = $mysql->prepare("DELETE FROM `surveys` WHERE `id` = ?");
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
Message::addMessage("A kérdőív törlése sikeres!", MessageType::success);
header("Location: list.survey.php");
?>