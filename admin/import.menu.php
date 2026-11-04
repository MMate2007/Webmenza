<?php
require_once "../config.php";
use PhpOffice\PhpSpreadsheet\Shared\Date;
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
if (isset($_FILES["spreadsheet"])) {
    if (isset($_POST["diets"])) {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($_FILES["spreadsheet"]["tmp_name"]);
    $sheet = $spreadsheet->getSheet(0);
    $rowGenerator = $sheet->rangeToArrayYieldRows("A2:".$sheet->getHighestDataColumn()."2");
    foreach ($rowGenerator as $row) {
        $astart = array_search("A menü", $row);
        $bstart = array_search("B menü", $row);
    }
    $rowGenerator = $sheet->rangeToArrayYieldRows("A3:".$sheet->getHighestDataColumn().$sheet->getHighestDataRow());
    $mysql->begin_transaction();
    try {
    $stmt = $mysql->prepare("INSERT INTO `menu`(`date`, `id`, `description`) VALUES (?,?,?)");
    $stmt->bind_param("sis", $date, $id, $description);
    $dietstmt = $mysql->prepare("INSERT INTO `menudiets`(`menuDate`, `menuId`, `dietId`) VALUES (?,?,?)");
    $dietstmt->bind_param("sii", $date, $id, $dietid);
    foreach ($rowGenerator as $row) {
        if (strtotime($row[5])) {
        if ($row[0] !== $row[5]) {
            throw new Exception("Érvénytelen adatokkal rendelkező táblázat! Valamelyik sorban a két dátum nem egyezik!");
        } }
        if ($row[$astart] != null && $row[$astart+1] != null)
{        $date = date_format(date_create($row[0]), "Y-m-d");
        $id = 1;
        $description = $row[$astart]."\n".$row[$astart+1]."\n".$row[$astart+2];
        $stmt->execute();
        foreach ($_POST["diets"] as $dietid) {
            $dietstmt->execute();
        }
}       
        if ($row[$bstart] != null && $row[$bstart+1] != null) {
        $id = 2;
        $description = $row[$bstart]."\n".$row[$bstart+1]."\n".$row[$bstart+2];
        $stmt->execute();
        foreach ($_POST["diets"] as $dietid) {
            $dietstmt->execute();
        }
        }
    }
    $mysql->commit();
    Message::addMessage("Menü importálása sikeres!", MessageType::success);
    } catch (mysqli_sql_exception $e) {
        $mysql->rollback();
        throw $e;
    } catch (Exception $e) {
        $mysql->rollback();
        Message::addMessage($e->getMessage(), MessageType::danger);
        Message::addMessage("Az importálás közben fellépő hibák miatt az importálás sikertelen.", MessageType::danger);
    }
    } else {
        Message::addMessage("Legalább egy étrendet ki kell jelölni!", MessageType::danger);
    }
}
$diets = $mysql->query("SELECT * FROM `diets`")->fetch_all(MYSQLI_ASSOC);
echo $twig->render("import.menu.html.twig", ["diets" => $diets]);
?>