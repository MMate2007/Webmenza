<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
if (isset($_POST["date"])) {
    $menuidstmt = $mysql->prepare("SELECT MAX(`id`) FROM `menu` WHERE `date` = ? AND `mealId` = ?");
    $menuidstmt->bind_param("si", $_POST["date"], $_POST["meal"]);
    $menuidstmt->execute();
    $result = $menuidstmt->get_result();
    $menuid = $result->fetch_row()[0] + 1;
    $query = $mysql->prepare("INSERT INTO `menu`(`date`, `mealId`, `id`, `description`) VALUES (?,?,?,?)");
    $query->bind_param("siis", $_POST["date"], $_POST["meal"], $menuid, $_POST["menu"]);
    if ($query->execute()) {
        Message::addMessage("Menü létrehozása sikeres!", MessageType::success);
    }
}
$meals = $mysql->query("SELECT `id`, `name` FROM `meals`")->fetch_all(MYSQLI_ASSOC);
echo $twig->render("add.menu.html.twig", ["meals" => $meals]);
?>