<?php
require_once "config.php";
authUser();
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$stmt = $mysql->prepare("SELECT CASE WHEN CURDATE() BETWEEN `start` AND `end` THEN TRUE ELSE FALSE END AS `fillable` FROM `deadlines` WHERE ? BETWEEN `from` AND `to`");
$stmt->execute([$_GET["date"]]);
$allowedtofill = $stmt->get_result()->fetch_row();
if ($allowedtofill !== null) {
    $allowedtofill = (bool)$allowedtofill[0];
} else if ($allowedtofill === null) {
    $allowedtofill = true;
}
if ($allowedtofill == false) {
    $stmt = $mysql->prepare("INSERT INTO `modifications`(`userId`, `date`, `value`) VALUES (?,?,NULL)");
    $stmt->bind_param("is", $_SESSION["userId"], $_GET["date"]);
    $stmt->execute();
    Message::addMessage("Lemondási kérelem sikeresen rögzítve!", MessageType::success);
    Message::addMessage("<i>Figyelem!</i><br>A kérelem rögzítve lett, azonban ahhoz, hogy érvénybe lépjen egy ügyintézőnek el kell fogadnia. <b>Elfogadás hiányában</b> a kért módosítás <b>nem lép érvénybe!</b>", MessageType::warning);
    header("Location: set.menu.php?date=".$_GET["date"]);
}
?>