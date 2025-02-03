<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
if (isset($_POST["name"])) {
    $stmt = $mysql->prepare("UPDATE `groups` SET `name`=? WHERE `id` = ?");
    $stmt->bind_param("si", $_POST["name"], $_GET["id"]);
    $stmt->execute();
    Message::addMessage("Sikeres mentés!", MessageType::success);
}
$stmt = $mysql->prepare("SELECT `name` FROM `groups` WHERE `id` = ?");
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
$group = $stmt->get_result()->fetch_row();
echo $twig->render("edit.group.html.twig", ["group" => $group]);
?>