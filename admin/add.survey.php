<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
if (isset($_POST["title"])) {
    $query = $mysql->prepare("INSERT INTO `surveys`(`title`, `description`, `start`, `end`, `anonim`) VALUES (?,?,?,?,?)");
    $anonim = $_POST["notanonim"] ?? 1;
    $query->bind_param("ssssi", $_POST["title"], $_POST["description"], $_POST["start"], $_POST["end"], $anonim);
    if ($query->execute()) {
        Message::addMessage("Kérdőív létrehozása sikeres!", MessageType::success);
        header("Location: add.question.survey.php?id=".$mysql->insert_id);
    }
}
echo $twig->render("add.survey.html.twig");
?>