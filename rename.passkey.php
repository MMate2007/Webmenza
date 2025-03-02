<?php
require_once "config.php";
authUser();
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$id = base64_decode($_GET["id"]);
if (isset($_POST["name"])) {
$stmt = $mysql->prepare("UPDATE `passkeys` SET `name`=? WHERE `id` = ?");
$stmt->bind_param("ss", $_POST["name"], $id);
$stmt->execute();
Message::addMessage("Sikeres átnevezés!", MessageType::success);
header("Location: list.passkey.php");
}
$stmt = $mysql->prepare("SELECT `name` FROM `passkeys` WHERE `id` = ?");
$stmt->bind_param("s", $id);
$stmt->execute();
$result = $stmt->get_result()->fetch_row()[0];
echo $twig->render("rename.passkey.html.twig", ["name" => $result]);
?>