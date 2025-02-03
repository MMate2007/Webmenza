<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
if (isset($_GET["withusers"]) || isset($_GET["onlyusers"])) {
    $stmt = $mysql->prepare("DELETE FROM `users` WHERE `groupId` = ?");
    $stmt->bind_param("i", $_GET["id"]);
    $stmt->execute();
    Message::addMessage("A csoportba tartozó felhasználók törlése sikeres!", MessageType::success);
}
if (!isset($_GET["onlyusers"])) {
    $stmt = $mysql->prepare("DELETE FROM `groups` WHERE `id` = ?");
    $stmt->bind_param("i", $_GET["id"]);
    $stmt->execute();
    Message::addMessage("A csoport törlése sikeres!", MessageType::success);
}
header("Location: list.group.php");
?>