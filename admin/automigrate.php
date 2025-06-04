<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
if (isset($_POST["order"])) {
    $migratepattern = $_POST["order"];
    $migrateorder = array_reverse(explode(";", $migratepattern));
    $migrateorderid = [];
    $stmt = $mysql->prepare("SELECT `id` FROM `groups` WHERE `name` LIKE ? ORDER BY `name`");
    $stmt->bind_param("s", $groupName);
    foreach ($migrateorder as $group) {
        $groupName = $group."%";
        $stmt->execute();
        $res = $stmt->get_result();
        $ids = [];
        while ($row = $res->fetch_array()) {
            $ids[] = $row["id"];
        }
        $migrateorderid[] = $ids;
    }
    $stmt = $mysql->prepare("DELETE FROM `users` WHERE `groupId` = ?");
    $stmt->bind_param("i", $groupid);
    foreach ($migrateorderid[0] as $id) {
        $groupid = $id;
        $stmt->execute();
    }
    $stmt = $mysql->prepare("UPDATE `users` SET `groupId`=? WHERE `groupId` = ?");
    $stmt->bind_param("ii", $to, $from);
    for ($i = 0;$i<count($migrateorder)-1;$i++) {
        foreach ($migrateorderid[$i] as $key => $id) {
            $to = $id;
            $from = $migrateorderid[$i+1][$key];
            $stmt->execute();
        }
    }
    Message::addMessage("Sikeres évfolyamléptetés!", MessageType::success);
}
echo $twig->render("automigrate.html.twig");
?>