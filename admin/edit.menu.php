<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
if (isset($_POST["menu"])) {
    $stmt = $mysql->prepare("UPDATE `menu` SET `description`=? WHERE `date` = ? AND `id` = ?  AND `mealId` = ?");
    $stmt->bind_param("ssii", $_POST["menu"], $_GET["date"], $_GET["id"], $_GET["mealid"]);
    $stmt->execute();
    Message::addMessage("Sikeres módosítás!", MessageType::success);
}
$stmt = $mysql->prepare("SELECT `description` FROM `menu` WHERE `date` = ? AND `id` = ? AND `mealId` = ?");
$stmt->bind_param("sii", $_GET["date"], $_GET["id"], $_GET["mealid"]);
$stmt->execute();
$menu = $stmt->get_result()->fetch_row()[0];
$meals = $mysql->query("SELECT `id`, `name` FROM `meals`")->fetch_all(MYSQLI_ASSOC);
echo $twig->render("edit.menu.html.twig", ["menu" => $menu, "date" => $_GET["date"], "id" => $_GET["id"], "mealId" => $_GET["mealid"], "meals" => $meals]);
?>