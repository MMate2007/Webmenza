<?php
require_once "config.php";
authUser();
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$stmt = $mysql->prepare("SELECT `id`, `name` FROM `passkeys` WHERE `userId` = ?");
$stmt->bind_param("i", $_SESSION["userId"]);
$stmt->execute();
$passkeys = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo $twig->render("list.passkey.html.twig", ["keys" => $passkeys]);
?>