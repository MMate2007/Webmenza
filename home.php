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
echo $twig->render("home.html.twig", ["todayMenu" => $todayMenu, "modifications" => $modifications]);
?>