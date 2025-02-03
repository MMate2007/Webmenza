<?php
require_once "config.php";
authUser(allownonregistered: true);
if (isset($_POST["password"])) {
    if ($_POST["passwordOld"] == $_POST["password"]) {
        Message::addMessage("Az új jelszó nem egyezhet meg a régivel.", MessageType::danger);
    } else {
        $mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
        $mysql->query("SET NAMES utf8");
        $stmt = $mysql->prepare("SELECT `password` FROM `users` WHERE `id` = ?");
        $stmt->bind_param("i", $_SESSION["userId"]);
        $stmt->execute();
        $passwordOld = $stmt->get_result()->fetch_row()[0];
        if (password_verify($_POST["passwordOld"], $passwordOld)) {
            if ($_POST["password"] == $_POST["pass2"]) {
                $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
                $stmt = $mysql->prepare("UPDATE `users` SET `password`=? WHERE `id` = ?");
                $stmt->bind_param("si", $password, $_SESSION["userId"]);
                $stmt->execute();
                if (isset($_SESSION["registered"])) {
                if ($_SESSION["registered"] == false) {
                    $stmt = $mysql->prepare("UPDATE `users` SET `registered`=1 WHERE `id` = ?");
                    $stmt->bind_param("i", $_SESSION["userId"]);
                    $stmt->execute();
                    unset($_SESSION["registered"]); 
                } }
                session_regenerate_id();
                Message::addMessage("A jelszó módosítása sikeres!", MessageType::success);
                header("Location: login.php");
                exit;
            } else {
                Message::addMessage("A két új jelszó nem egyezik meg!", MessageType::danger);
            }
        } else {
            Message::addMessage("A jelenlegi jelszó hibás!", MessageType::danger);
        }
    }
}
echo $twig->render("modify.password.html.twig");
?>