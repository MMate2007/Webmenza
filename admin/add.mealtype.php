<?php
require_once "../config.php";
authUser(1);
if (isset($_POST["name"])) {
    $mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
    $mysql->query("SET NAMES utf8");
    $result = $mysql->prepare("INSERT INTO `meals` (`name`) VALUES (?)")->execute([$_POST["name"]]);
    if ($result) {
        Message::addMessage("Étkezés létrehozása sikeres!", MessageType::success);
    }
}
echo $twig->render("add.mealtype.html.twig");
?>