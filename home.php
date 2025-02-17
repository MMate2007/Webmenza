<?php
require_once "config.php";
authUser();
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$stmt = $mysql->prepare("SELECT `description` FROM `menu` INNER JOIN `choices` ON `menu`.`date` = `choices`.`date` AND `menu`.`id` = `choices`.`menuId` WHERE `choices`.`date` = CURRENT_DATE AND `choices`.`userId` = ?");
$stmt->bind_param("i", $_SESSION["userId"]);
$stmt->execute();
$todayMenu = $stmt->get_result()->fetch_row();
$stmt = $mysql->prepare("SELECT `date`, `approved` FROM `modifications` WHERE `userId` = ? ORDER BY `date`");
$stmt->bind_param("i", $_SESSION["userId"]);
$stmt->execute();
$modifications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt = $mysql->prepare("SELECT `from`, `to`, `end`, COUNT(`choices`.`date`) AS `choices`, COUNT(`menu`.`date`) AS `dates` FROM `deadlines` LEFT JOIN `menu` ON `menu`.`date` BETWEEN `deadlines`.`from` AND `deadlines`.`to` LEFT JOIN `choices` ON `choices`.`date` = `menu`.`date` AND `choices`.`userId` = ? WHERE CURRENT_DATE BETWEEN `start` AND `end` GROUP BY `deadlines`.`id` ORDER BY `deadlines`.`end`");
$stmt->bind_param("i", $_SESSION["userId"]);
$stmt->execute();
$deadlines = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo $twig->render("home.html.twig", ["todayMenu" => $todayMenu, "modifications" => $modifications, "deadlines" => $deadlines, "days" => fetchDatesForCal(date_create()->format("Y-m"))]);
?>