<?php
require_once "config.php";
authUser();
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$stmt = $mysql->prepare("DELETE FROM `modifications` WHERE `userId` = ? AND `date` = ?");
$stmt->bind_param("is", $_SESSION["userId"], $_GET["date"]);
$stmt->execute();
Message::addMessage("Kérelem visszavonása sikeres!", MessageType::success);
header("Location: home.php");
?>