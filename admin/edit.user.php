<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
if (isset($_POST["password"]) && isset($_POST["password2"])) {
    if ($_POST["password"] != "") {
        if ($_POST["password"] == $_POST["password2"]) {
            $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
            $stmt = $mysql->prepare("UPDATE `users` SET `password`=? WHERE `id` = ?");
            $stmt->bind_param("si", $password, $_GET["id"]);
            $stmt->execute();
            Message::addMessage("Sikeres jelszómódosítás!", MessageType::success);
        } else {
            Message::addMessage("A két jelszó nem egyezik meg!", MessageType::danger);
        }
    }
}
if (isset($_POST["name"])) {
    $stmt = $mysql->prepare("UPDATE `users` SET `name`=?,`groupId`=?,`registered`=? WHERE `id` = ?");
    $group = ($_POST["group"] == "") ? null : (int)$_POST["group"];
    $registered = $_POST["registered"] ?? 0;
    $stmt->bind_param("siii", $_POST["name"], $group, $registered, $_GET["id"]);
    $stmt->execute();
    Message::addMessage("Sikeres mentés!", MessageType::success);
}
$stmt = $mysql->prepare("SELECT `users`.`name`, `groupId`, `registered` FROM `users` WHERE `users`.`id` = ?");
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
$user = $stmt->get_result()->fetch_row();
$groups = $mysql->query("SELECT * FROM `groups`")->fetch_all(MYSQLI_ASSOC);
echo $twig->render("edit.user.html.twig", ["user" => $user, "groups" => $groups]);
?>