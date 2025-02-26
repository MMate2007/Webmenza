<?php
require_once "config.php";
authUser();
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$input = file_get_contents('php://input');
switch ($_SERVER["REQUEST_METHOD"]) {
    case "POST":
        $stmt = $mysql->prepare("INSERT INTO `notificationsubscriptions`(`userId`, `data`) VALUES (?,?)");
        $stmt->bind_param("is", $_SESSION["userId"], $input);
        $stmt->execute();
        break;
}
?>