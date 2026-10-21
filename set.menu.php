<?php
require_once "config.php";
authUser();
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$nextdatestmt = $mysql->prepare("SELECT `menuDate` FROM `menudiets` LEFT JOIN `deadlines` ON `menudiets`.`menuDate` BETWEEN `deadlines`.`from` AND `deadlines`.`to` WHERE `menuDate` > ? AND ((CURDATE() BETWEEN `start` AND `end`) OR `start` IS NULL) AND `dietId` IN (SELECT `dietId` FROM `users` WHERE `users`.`id` = ?) LIMIT 1");
if (isset($_GET["date"]))
{
    $date = $_GET["date"];
}
$stmt = $mysql->prepare("SELECT CASE WHEN CURDATE() BETWEEN `start` AND `end` THEN TRUE ELSE FALSE END AS `fillable` FROM `deadlines` WHERE ? BETWEEN `from` AND `to` ORDER BY `fillable` DESC");
$stmt->execute([$date]);
$allowedtofill = $stmt->get_result()->fetch_row();
if ($allowedtofill !== null) {
    $allowedtofill = (bool)$allowedtofill[0];
} else if ($allowedtofill === null) {
    $allowedtofill = true;
}
if (isset($_POST["date"]) && $allowedtofill) {
    if (!isset($_POST["meal"])) {
        $stmt = $mysql->prepare("DELETE FROM `choices` WHERE `userId` = ? AND `date` = ?");
        $stmt->bind_param("is", $_SESSION["userId"], $_POST["date"]);
        $stmt->execute();
        Message::addMessage("Választás sikeresen törölve!", MessageType::success);
    } else {
    $meal = ($_POST["meal"] == "null") ? null : $_POST["meal"];
    $stmt = $mysql->prepare("INSERT INTO `choices`(`userId`, `date`, `menuId`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE `menuId` = ?");
    $stmt->bind_param("isii", $_SESSION["userId"], $_POST["date"], $meal, $meal);
    if ($stmt->execute()) {
        Message::addMessage("Választás sikeresen mentve!", MessageType::success);
    }
    $nextdatestmt->execute([$_POST["date"], $_SESSION["userId"]]);
    $row = $nextdatestmt->get_result()->fetch_row();
    if ($row !== null) {
        header("Location: set.menu.php?date=".$row[0]);
        exit;
    } else {
       Message::addMessage("A lista végére értél. Nincs már több dátum.", MessageType::info);
    }
    }
}
if ($allowedtofill === false) {
    $stmt = $mysql->prepare("SELECT `value` FROM `modifications` WHERE `userId` = ? AND `date` = ?");
    $stmt->bind_param("is", $_SESSION["userId"], $date);
    $stmt->execute();
    $requests = $stmt->get_result()->fetch_row();
    if ($requests != null) {
        $requests=$requests[0] ?? 0;
    }
    Message::addMessage("A kitöltési határidő erre a napra lejárt!", MessageType::danger);
}
$stmt = $mysql->prepare("SELECT `menuId` FROM `choices` WHERE `date` = ? AND `userId` = ?");
$stmt->bind_param("si", $date, $_SESSION["userId"]);
$stmt->execute();
$choice = $stmt->get_result()->fetch_row();
if ($choice !== null) $choice = $choice[0]; else $choice = 0;
$stmt = $mysql->prepare("SELECT `id`, `description` FROM `menu` INNER JOIN `menudiets` ON `menu`.`date` = `menudiets`.`menuDate` AND `menu`.`id` = `menudiets`.`menuId` WHERE `date` = ? AND `dietId` IN (SELECT `dietId` FROM `users` WHERE `users`.`id` = ?)");
$stmt->execute([$date, $_SESSION["userId"]]);
$menu = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo $twig->render("set.menu.html.twig", ["date" => $date, "id" => $choice, "menu" => $menu ?? null, "fillable" => $allowedtofill, "requests" => $requests ?? null, "days" => fetchDatesForCal(date_create($date)->format("Y-m")), "enableModificationRequests" => $enableModificationRequests]);
?>