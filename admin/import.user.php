<?php
set_time_limit(0);
require_once "../config.php";
authUser(1);
if (isset($_FILES["spreadsheet"])) {
    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($_FILES["spreadsheet"]["tmp_name"]);
    $sheet = $spreadsheet->getSheet(0);
    $rowGenerator = $sheet->rangeToArrayYieldRows("A2:C".$sheet->getHighestDataRow());
    $mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
    $mysql->query("SET NAMES utf8");
    $result = $mysql->query("SELECT `id`, `name` FROM `groups`");
    while ($row = $result->fetch_array()) {
        $groups[$row[0]] = $row[1];
    }
    $mysql->begin_transaction();
    try {
    $userstmt = $mysql->prepare("INSERT INTO `users`(`name`, `password`, `groupId`) VALUES (?,?,?)");
    $userstmt->bind_param("ssi", $name, $password, $id);
    $groupstmt = $mysql->prepare("INSERT INTO `groups`(`name`) VALUES (?)");
    $groupstmt->bind_param("s", $groupname);
    foreach ($rowGenerator as $row) {
        $id = array_search($row[1], $groups);
        if (!$id) {
            $groupname = $row[1];
            $groupstmt->execute();
            $id = $mysql->insert_id;
            $groups[$id] = $row[1];
        }
        $name = $row[0];
        $password = password_hash($row[2], PASSWORD_DEFAULT);
        $userstmt->execute();
    }
    $mysql->commit();
    Message::addMessage("Felhasználók importálása sikeres!", MessageType::success);
    } catch (mysqli_sql_exception $e) {
        $mysql->rollback();
        throw $e;
    }
}
echo $twig->render("import.user.html.twig");
?>