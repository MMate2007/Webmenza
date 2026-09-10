<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
if (isset($_POST["date"])) {
if (isset($_POST["diets"])) {
    $menuidstmt = $mysql->prepare("SELECT MAX(`id`) FROM `menu` WHERE `date` = ?");
    $menuidstmt->execute([$_POST["date"]]);
    $result = $menuidstmt->get_result();
    $menuid = $result->fetch_row()[0] + 1;
    $query = $mysql->prepare("INSERT INTO `menu`(`date`, `id`, `description`) VALUES (?,?,?)");
    $query->bind_param("sis", $_POST["date"], $menuid, $_POST["menu"]);
    if ($query->execute()) {
        $stmt = $mysql->prepare("INSERT INTO `menudiets`(`menuDate`, `menuId`, `dietId`) VALUES (?,?,?)");
        $stmt->bind_param("sii", $_POST["date"], $menuid, $dietid);
        foreach ($_POST["diets"] as $dietid) {
            $stmt->execute();
        }
        Message::addMessage("Menü létrehozása sikeres!", MessageType::success);
    }
}else{
    Message::addMessage("Legalább egy étrendet ki kell jelölni!", MessageType::danger);
}}
$diets = $mysql->query("SELECT * FROM `diets`")->fetch_all(MYSQLI_ASSOC);
echo $twig->render("add.menu.html.twig", ["diets" => $diets]);
?>