<?php
require_once "../config.php";
authUser(1);
if (isset($_FILES["spreadsheet"])) {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($_FILES["spreadsheet"]["tmp_name"]);
    $sheet = $spreadsheet->getSheet(0);
    $rowGenerator = $sheet->rangeToArrayYieldRows("A2:".$sheet->getHighestDataColumn()."2");
    $mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
    $mysql->query("SET NAMES utf8");
    foreach ($rowGenerator as $row) {
        $astart = array_search("A menü", $row);
        $bstart = array_search("B menü", $row);
    }
    $rowGenerator = $sheet->rangeToArrayYieldRows("A3:".$sheet->getHighestDataColumn().$sheet->getHighestDataRow());
    $mysql->begin_transaction();
    try {
    $stmt = $mysql->prepare("INSERT INTO `menu`(`date`, `id`, `description`) VALUES (?,?,?)");
    $stmt->bind_param("sis", $date, $id, $description);
    foreach ($rowGenerator as $row) {
        if ($row[$astart] != null && $row[$astart+1] != null)
{        $date = date_format(date_create($row[0]), "Y-m-d");
        $id = 1;
        $description = $row[$astart]."\n".$row[$astart+1]."\n".$row[$astart+2];
        $stmt->execute();
}       
        if ($row[$bstart] != null && $row[$bstart+1] != null) {
        $id = 2;
        $description = $row[$bstart]."\n".$row[$bstart+1]."\n".$row[$bstart+2];
        $stmt->execute(); }
    }
    $mysql->commit();
    Message::addMessage("Menü importálása sikeres!", MessageType::success);
    } catch (mysqli_sql_exception $e) {
        $mysql->rollback();
        throw $e;
    }
}
echo $twig->render("import.menu.html.twig");
?>