<?php
require_once "../config.php";
authUser(1);
if (isset($_POST["name"])) {
    if (!preg_match("/^[^\/\[\]\?\*\\']*$/", $_POST["name"])) {
        Message::addMessage("A csoport neve nem tartalmazhatja a [ ] / ? * \ ' karaktereket!", MessageType::danger);
    } else {
    $mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
    $mysql->query("SET NAMES utf8");
    $result = $mysql->prepare("INSERT INTO `groups` (`name`) VALUES (?)")->execute([$_POST["name"]]);
    if ($result) {
        Message::addMessage("Csoport létrehozása sikeres!", MessageType::success);
    }
    }
}
echo $twig->render("add.group.html.twig");
?>