<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$stmt = $mysql->prepare("SELECT `name` FROM `groups` WHERE `id` = ?");
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
$groupname = $stmt->get_result()->fetch_row()[0];
$stmt = $mysql->prepare("SELECT `id`, `name`, `registered` FROM `users` WHERE `groupId` = ? ORDER BY `name`");
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
$users = $stmt->get_result()->fetch_all();
$stmt = $mysql->prepare("SELECT `id`, `name`, !EXISTS(SELECT 1 FROM `excludegroupsfrommeals` WHERE `groupId` = ? AND `mealId` = `meals`.`id`) AS `allowed` FROM `meals`");
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
$meals = $stmt->get_result()->fetch_all(MYSQLI_BOTH);
echo $twig->render("view.group.html.twig", ["users" => $users ?? null, "groupname" => $groupname, "groupId" => $_GET["id"], "meals" => $meals ?? null]);
?>