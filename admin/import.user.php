<?php
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
    $userstmt = $mysql->prepare("INSERT INTO `users`(`name`, `password`, `groupId`) VALUES (?,?,?)");
    $userstmt->bind_param("ssi", $name, $password, $id);
    foreach ($rowGenerator as $row) {
        $id = array_search($row[1], $groups);
        if (!$id) {
            $stmt = $mysql->prepare("INSERT INTO `groups`(`name`) VALUES (?)")->execute([$row[1]]);
            $id = $mysql->insert_id;
            $groups[$id] = $row[1];
        }
        $name = $row[0];
        $password = password_hash($row[2], PASSWORD_DEFAULT);
        $userstmt->execute();
    }
    Message::addMessage("Felhasználók importálása sikeres!", MessageType::success);
}
echo $twig->render("import.user.html.twig");
?>