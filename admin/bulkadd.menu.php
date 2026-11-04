<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
if (isset($_POST["start"]) && isset($_POST["end"])) {
    $start = new DateTime($_POST["start"]);
    $end = new DateTime($_POST["end"]);
    if ($end >= $start) {
if (isset($_POST["diets"])) {
    $end->modify("+1 days");
    $interval = new DateInterval("P1D");
    $dates = new DatePeriod($start, $interval, $end);
    $mysql->begin_transaction();
    try {
    $menuidstmt = $mysql->prepare("SELECT MAX(`id`) FROM `menu` WHERE `date` = ?");
    $menuinsertstmt = $mysql->prepare("INSERT INTO `menu`(`date`, `id`, `description`) VALUES (?,?,?)");
    $menuinsertstmt->bind_param("sis", $dateformat, $menuid, $_POST["menu"]);
    $menudietsstmt = $mysql->prepare("INSERT INTO `menudiets`(`menuDate`, `menuId`, `dietId`) VALUES (?,?,?)");
    $menudietsstmt->bind_param("sii", $dateformat, $menuid, $dietid);
    foreach ($dates as $date) {
        if (in_array($date->format("N"), $_POST["days"])) {
            $dateformat = $date->format("Y-m-d");
            $menuidstmt->execute([$dateformat]);
            $result = $menuidstmt->get_result();
            $menuid = $result->fetch_row()[0] + 1;
            if ($menuinsertstmt->execute()) {
                foreach ($_POST["diets"] as $dietid) {
                    $menudietsstmt->execute();
                }
            }
        }
    }
    $mysql->commit();
    Message::addMessage("Sikeres hozzáadás!", MessageType::success);
    } catch (mysqli_sql_exception $e) {
        Message::addMessage("Sikertelen hozzáadás!", MessageType::danger);
        $mysql->rollback();
        throw $e;
    }
}else{
    Message::addMessage("Legalább egy étrendet ki kell jelölni!", MessageType::danger);
}} else {
    Message::addMessage("Az utolsó nap nem lehet az első napnál korábban!", MessageType::danger);
}}
$diets = $mysql->query("SELECT * FROM `diets`")->fetch_all(MYSQLI_ASSOC);
echo $twig->render("bulkadd.menu.html.twig", ["diets" => $diets]);
?>