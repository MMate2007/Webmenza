<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$stmt = $mysql->prepare("DELETE FROM `menu` WHERE `date` = ? AND `id` = ? AND `mealId` = ?");
$stmt->bind_param("sii", $_GET["date"], $_GET["id"], $_GET["mealid"]);
$stmt->execute();
Message::addMessage("A menü törlése sikeres!", MessageType::success);
header("Location: list.menu.php");
?>