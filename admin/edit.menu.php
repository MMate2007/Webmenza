<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
if (isset($_POST["menu"])) {
    $stmt = $mysql->prepare("UPDATE `menu` SET `description`=? WHERE `date` = ? AND `id` = ?");
    $stmt->bind_param("ssi", $_POST["menu"], $_GET["date"], $_GET["id"]);
    $stmt->execute();
    Message::addMessage("Sikeres módosítás!", MessageType::success);
}
$stmt = $mysql->prepare("SELECT `description` FROM `menu` WHERE `date` = ? AND `id` = ?");
$stmt->bind_param("si", $_GET["date"], $_GET["id"]);
$stmt->execute();
$menu = $stmt->get_result()->fetch_row()[0];
echo $twig->render("edit.menu.html.twig", ["menu" => $menu, "date" => $_GET["date"], "id" => $_GET["id"]]);
?>