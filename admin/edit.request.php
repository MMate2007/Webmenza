<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
if ($_GET["state"] == 1) {
    $stmt = $mysql->prepare("SELECT `value` FROM `modifications` WHERE `userId` = ? AND `date` = ?");
    $stmt->bind_param("is", $_GET["userId"], $_GET["date"]);
    $stmt->execute();
    $value = $stmt->get_result()->fetch_row()[0];
    $stmt = $mysql->prepare("UPDATE `choices` SET `menuId`=? WHERE `userId` = ? AND `date` = ?");
    $stmt->bind_param("iis", $value, $_GET["userId"], $_GET["date"]);
    $stmt->execute();
    $stmt = $mysql->prepare("DELETE FROM `modifications` WHERE `userId` = ? AND `date` = ?");
    $stmt->bind_param("is", $_GET["userId"], $_GET["date"]);
    $stmt->execute();
    Message::addMessage("Kérelem elfogadása és a változások rögzítése sikeres!", MessageType::success);
    header("Location: list.requests.php");
} else if ($_GET["state"] == 0) {
    $stmt = $mysql->prepare("UPDATE `modifications` SET `approved`=FALSE WHERE `userId` = ? AND `date` = ?");
    $stmt->bind_param("is", $_GET["userId"], $_GET["date"]);
    $stmt->execute();
    Message::addMessage("Kérelem elutasítása sikeres!", MessageType::success);
    header("Location: list.requests.php");
}
?>