<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$stmt = $mysql->prepare("SELECT `name` FROM `diets` WHERE `id` = ?");
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
$dietname = $stmt->get_result()->fetch_row()[0];
$stmt = $mysql->prepare("SELECT `users`.`id`, `users`.`name`, `registered`, `groupId`, `groups`.`name` FROM `users` LEFT JOIN `groups` ON `users`.`groupId` = `groups`.`id` WHERE `dietId` = ? ORDER BY `users`.`name`");
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_array()) {
    $users[] = $row;
}
echo $twig->render("view.diet.html.twig", ["users" => $users ?? null, "dietname" => $dietname, "dietId" => $_GET["id"]]);
?>