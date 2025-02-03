<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
if (isset($_POST["date"])) {
    $menuidstmt = $mysql->prepare("SELECT MAX(`id`) FROM `menu` WHERE `date` = ?");
    $menuidstmt->execute([$_POST["date"]]);
    $result = $menuidstmt->get_result();
    $menuid = $result->fetch_row()[0] + 1;
    $query = $mysql->prepare("INSERT INTO `menu`(`date`, `id`, `description`) VALUES (?,?,?)");
    $query->bind_param("sis", $_POST["date"], $menuid, $_POST["menu"]);
    if ($query->execute()) {
        Message::addMessage("Menü létrehozása sikeres!", MessageType::success);
    }
}
echo $twig->render("add.menu.html.twig");
?>