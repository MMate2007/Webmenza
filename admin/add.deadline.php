<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
if (isset($_POST["start"])) {
    $query = $mysql->prepare("INSERT INTO `deadlines`(`from`, `to`, `start`, `end`) VALUES (?,?,?,?)");
    $query->execute([$_POST["from"], $_POST["to"], $_POST["start"], $_POST["end"]]);
    Message::addMessage("Határidő hozzáadása sikeres!", MessageType::success);
}
echo $twig->render("add.deadline.html.twig");
?>