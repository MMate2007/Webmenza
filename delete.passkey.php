<?php
require_once "config.php";
authUser();
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$stmt = $mysql->prepare("DELETE FROM `passkeys` WHERE `id` = ?");
$id = base64_decode($_GET["id"]);
$stmt->bind_param("s", $id);
$stmt->execute();
Message::addMessage("A jelkulcs törlése sikeres!", MessageType::success);
Message::addMessage("Ne felejtse el törölni az eszközből is a jelkulcsot!", MessageType::info);
header("Location: list.passkey.php");
?>