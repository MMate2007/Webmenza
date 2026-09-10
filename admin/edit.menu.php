<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
if (isset($_POST["menu"])) {
    if (isset($_POST["diets"])) {
    $mysql->begin_transaction();
    try {
    $stmt = $mysql->prepare("UPDATE `menu` SET `description`=? WHERE `date` = ? AND `id` = ?");
    $stmt->bind_param("ssi", $_POST["menu"], $_GET["date"], $_GET["id"]);
    $stmt->execute();
    $stmt = $mysql->prepare("DELETE FROM `menudiets` WHERE `menuDate` = ? AND `menuId` = ?");
    $stmt->bind_param("si", $_GET["date"], $_GET["id"]);
    $stmt->execute();
    $stmt = $mysql->prepare("INSERT INTO `menudiets`(`menuDate`, `menuId`, `dietId`) VALUES (?,?,?)");
    $stmt->bind_param("sii", $_GET["date"], $_GET["id"], $dietid);
    foreach ($_POST["diets"] as $dietid) {
        $stmt->execute();
    }
    $mysql->commit();
    Message::addMessage("Sikeres módosítás!", MessageType::success);
    } catch (mysqli_sql_exception $e) {
        $mysql->rollback();
        Message::addMessage("Sikertelen módosítás!", MessageType::danger);
        throw $e;
    }
    } else {
    Message::addMessage("Legalább egy étrendet ki kell jelölni!", MessageType::danger);
    }
}
$stmt = $mysql->prepare("SELECT `description`, JSON_ARRAYAGG(`menudiets`.`dietId`) AS `dietIds` FROM `menu` INNER JOIN `menudiets` ON `menu`.`date` = `menudiets`.`menuDate` AND `menu`.`id` = `menudiets`.`menuId` WHERE `date` = ? AND `id` = ? GROUP BY `menu`.`date`, `menu`.`id`");
$stmt->bind_param("si", $_GET["date"], $_GET["id"]);
$stmt->execute();
$menu = $stmt->get_result()->fetch_row();
$menu[1] = json_decode($menu[1]);
$diets = $mysql->query("SELECT * FROM `diets`")->fetch_all(MYSQLI_ASSOC);
echo $twig->render("edit.menu.html.twig", ["menu" => $menu[0], "date" => $_GET["date"], "id" => $_GET["id"], "dietIds" => $menu[1], "diets" => $diets]);
?>