<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
if (isset($_POST["from"]) && isset($_POST["to"])) {
    $stmt = $mysql->prepare("UPDATE `users` SET `groupId`=? WHERE `groupId` = ?");
    $stmt->bind_param("ii", $_POST["to"], $_POST["from"]);
    $stmt->execute();
    Message::addMessage("A felhasználók költöztetése sikeres!", MessageType::success);
    header("Location: view.group.php?id=".$_POST["to"]);
    exit;
}
$groups = $mysql->query("SELECT * FROM `groups`")->fetch_all(MYSQLI_ASSOC);
echo $twig->render("migrate.group.html.twig", ["groups" => $groups, "from" => $_GET["from"]]);
?>