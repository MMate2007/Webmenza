<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$stmt = $mysql->prepare("SELECT `from`, `to`, `start`, `end` FROM `deadlines` WHERE `id` = ?");
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
$data = $stmt->get_result()->fetch_row();
$stmt = $mysql->prepare("SELECT COUNT(DISTINCT `date`) FROM `menu` INNER JOIN `deadlines` ON `date` BETWEEN `from` AND `to` WHERE `deadlines`.`id` = ?");
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
$days = $stmt->get_result()->fetch_row()[0];
$users = $mysql->query("SELECT COUNT(*) FROM `users` LEFT JOIN `groups` ON `groupId` = `groups`.`id` WHERE `registered` = 1 AND `groups`.`name` NOT LIKE '\_%'")->fetch_row()[0];
$stmt = $mysql->prepare("SELECT COUNT(*) FROM `choices` INNER JOIN `deadlines` ON `date` BETWEEN `from` AND `to` WHERE `id` = ?");
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
$choices = $stmt->get_result()->fetch_row()[0];
echo $twig->render("view.deadline.html.twig", ["from" => $data[0], "to" => $data[1], "start" => $data[2], "end" => $data[3], "days" => $days, "users" => $users, "choices" => $choices]);
?>