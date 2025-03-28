<?php
require_once "config.php";
authUser();
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$nextdatestmt = $mysql->prepare("SELECT `date` FROM `menu` LEFT JOIN `deadlines` ON `menu`.`date` BETWEEN `deadlines`.`from` AND `deadlines`.`to` WHERE `date` > ? AND ((CURDATE() BETWEEN `start` AND `end`) OR `start` IS NULL) AND `mealId` NOT IN (SELECT `mealId` FROM `excludegroupsfrommeals` INNER JOIN `users` ON `users`.`groupId` = `excludegroupsfrommeals`.`groupId` AND `users`.`id` = ?) LIMIT 1");
if (isset($_GET["date"]))
{
    $date = $_GET["date"];
}
$stmt = $mysql->prepare("SELECT CASE WHEN CURDATE() BETWEEN `start` AND `end` THEN TRUE ELSE FALSE END AS `fillable` FROM `deadlines` WHERE ? BETWEEN `from` AND `to`");
$stmt->execute([$date]);
$allowedtofill = $stmt->get_result()->fetch_row();
if ($allowedtofill !== null) {
    $allowedtofill = (bool)$allowedtofill[0];
} else if ($allowedtofill === null) {
    $allowedtofill = true;
}
if (isset($_POST["date"]) && $allowedtofill) {
    $stmt = $mysql->prepare("SELECT DISTINCT `mealId` FROM `menu` WHERE `mealId` NOT IN (SELECT `mealId` FROM `excludegroupsfrommeals` INNER JOIN `users` ON `users`.`groupId` = `excludegroupsfrommeals`.`groupId` AND `users`.`id` = ?) AND `date` = ?");
    $stmt->bind_param("is", $_SESSION["userId"], $_POST["date"]);
    $stmt->execute();
    $meals = $stmt->get_result();
    $delstmt = $mysql->prepare("DELETE FROM `choices` WHERE `userId` = ? AND `date` = ? AND `mealId` = ?");
    $addstmt = $mysql->prepare("INSERT INTO `choices`(`userId`, `date`, `mealId`, `menuId`) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE `menuId` = ?");
    while ($row = $meals->fetch_array()) {
        if (!isset($_POST["meal-".$row["mealId"]])) {
            $delstmt->bind_param("isi", $_SESSION["userId"], $_POST["date"], $row["mealId"]);
            $delstmt->execute();
            Message::addMessage("Választás sikeresen törölve!", MessageType::success);
        } else {
            $choice = ($_POST["meal-".$row["mealId"]] == "null") ? null : $_POST["meal-".$row["mealId"]];
            $addstmt->bind_param("isiii", $_SESSION["userId"], $_POST["date"], $row["mealId"], $choice, $choice);
            if ($addstmt->execute()) {
                Message::addMessage("Választás sikeresen mentve!", MessageType::success);
            }
        }
    }
    $nextdatestmt->bind_param("si", $_POST["date"], $_SESSION["userId"]);
    $nextdatestmt->execute();
    $row = $nextdatestmt->get_result()->fetch_row();
    if ($row !== null) {
        header("Location: set.menu.php?date=".$row[0]);
        exit;
    } else {
       Message::addMessage("A lista végére értél. Nincs már több dátum.", MessageType::info);
    }
}
if ($allowedtofill === false) {
    $stmt = $mysql->prepare("SELECT `mealId` FROM `modifications` WHERE `userId` = ? AND `date` = ?");
    $stmt->bind_param("is", $_SESSION["userId"], $date);
    $stmt->execute();
    $result = $stmt->get_result();
    $requests = [];
    while ($row = $result->fetch_array()) {
        $requests = $row["mealId"];
    }
    Message::addMessage("A kitöltési határidő erre a napra lejárt!", MessageType::danger);
}
$stmt = $mysql->prepare("SELECT `mealId`, `menuId` FROM `choices` WHERE `date` = ? AND `userId` = ? ORDER BY `mealId`");
$stmt->bind_param("si", $date, $_SESSION["userId"]);
$stmt->execute();
$result = $stmt->get_result();
$choices = [];
while ($row = $result->fetch_array()) {
    $choices[$row["mealId"]] = $row["menuId"];
}
$stmt = $mysql->prepare("SELECT `id`, `name` FROM `meals` WHERE `id` NOT IN (SELECT `mealId` FROM `excludegroupsfrommeals` INNER JOIN `users` ON `users`.`groupId` = `excludegroupsfrommeals`.`groupId` AND `users`.`id` = ?)");
$stmt->bind_param("i", $_SESSION["userId"]);
$stmt->execute();
$result = $stmt->get_result();
$stmt = $mysql->prepare("SELECT `id`, `description` FROM `menu` WHERE `date` = ? AND `mealId` = ? ORDER BY `id`");
$stmt->bind_param("si", $date, $mealId);
$menu = [];
while ($meal = $result->fetch_array()) {
    $mealId = $meal["id"];
    $stmt->execute();
    $r = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $menu[$meal["id"]] = [
        "mealName" => $meal["name"],
        "menu" => $r
    ];
}
echo $twig->render("set.menu.html.twig", ["date" => $date, "choices" => $choices, "menu" => $menu ?? null, "fillable" => $allowedtofill, "requests" => $requests ?? null, "days" => fetchDatesForCal(date_create($date)->format("Y-m")), "enableModificationRequests" => $enableModificationRequests]);
?>