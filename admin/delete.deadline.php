<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$stmt = $mysql->prepare("UPDATE `choices` INNER JOIN `modifications` ON `choices`.`date` = `modifications`.`date` AND `choices`.`userId` = `modifications`.`userId` SET `choices`.`menuId` = `modifications`.`value` WHERE (`modifications`.`approved` != 1 OR `modifications`.`approved` IS NULL) AND `modifications`.`date` BETWEEN (SELECT `from` FROM `deadlines` WHERE `id` = ?) AND (SELECT `to` FROM `deadlines` WHERE `id` = ?)");
$stmt->bind_param("ii", $_GET["id"], $_GET["id"]);
$stmt->execute();
$stmt = $mysql->prepare("DELETE `deadlines`, `modifications` FROM `deadlines` LEFT JOIN `modifications` ON `modifications`.`date` BETWEEN `deadlines`.`from` AND `deadlines`.`to` WHERE `deadlines`.`id` = ?");
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
Message::addMessage("A kapcsolódó módosítási kérelmek elfogadásra kerültek!", MessageType::warning);
Message::addMessage("A határidő törlése sikeres!", MessageType::success);
header("Location: list.deadline.php");
?>