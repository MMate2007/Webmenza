<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$stmt = $mysql->prepare("SELECT `users`.`id`, `users`.`name`, `groups`.`name`, `groupId` FROM `users` LEFT JOIN `groups` ON `users`.`groupId` = `groups`.`id` WHERE `users`.`id` = ?");
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
$userdata = $stmt->get_result()->fetch_row();
$stmt = $mysql->prepare("SELECT `date`, `menuId` FROM `choices` WHERE `userId` = ?");
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
$choicedata = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo $twig->render("view.user.html.twig", ["user" => $userdata, "choices" => $choicedata]);
?>