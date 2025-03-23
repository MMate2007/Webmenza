<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
if ($_GET["state"] == 1) {
    $stmt = $mysql->prepare("DELETE FROM `excludegroupsfrommeals` WHERE `mealId` = ? AND `groupId` = ?");
    $stmt->bind_param("ii", $_GET["meal"], $_GET["group"]);
    $stmt->execute();
    Message::addMessage("Étkezés sikeresen engedélyezve a csoport számára!", MessageType::success);
    header("Location: view.group.php?id=".$_GET["group"]);
    exit;
} else if ($_GET["state"] == 0) {
    $stmt = $mysql->prepare("INSERT INTO `excludegroupsfrommeals`(`mealId`, `groupId`) VALUES (?,?)");
    $stmt->bind_param("ii", $_GET["meal"], $_GET["group"]);
    $stmt->execute();
    Message::addMessage("Étkezés sikeresen letiltva a csoport számára!", MessageType::success);
    header("Location: view.group.php?id=".$_GET["group"]);
    exit;
}
?>