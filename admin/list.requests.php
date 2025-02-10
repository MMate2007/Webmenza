<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$stmt = $mysql->query("SELECT `userId`, `name`, `date`, `createdAt` FROM `modifications` INNER JOIN `users` ON `users`.`id` = `modifications`.`userId` WHERE `approved` IS NULL ORDER BY `createdAt`");
$modifications = $stmt->fetch_all(MYSQLI_ASSOC);
echo $twig->render("list.requests.html.twig", ["modifications" => $modifications]);
?>