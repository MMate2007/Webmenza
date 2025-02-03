<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$stmt = $mysql->prepare("SELECT `name` FROM `groups` WHERE `id` = ?");
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
$groupname = $stmt->get_result()->fetch_row()[0];
$stmt = $mysql->prepare("SELECT `id`, `name` FROM `users` WHERE `groupId` = ? AND `name` LIKE ?");
$search = "%";
if (isset($_GET["search"])) {
    $search = "%".$_GET["search"]."%";
}
$stmt->bind_param("is", $_GET["id"], $search);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_array()) {
    $users[] = $row;
}
echo $twig->render("view.group.html.twig", ["users" => $users ?? null, "groupname" => $groupname, "groupId" => $_GET["id"]]);
?>