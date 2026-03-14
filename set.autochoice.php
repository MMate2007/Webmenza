<?php
require_once "config.php";
authUser();
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
if (isset($_POST["reset"])) {
    $autochoice = [];
    foreach($_POST as $name => $value) {
        if ($name == "reset") continue;
        if ($name == "onlywhenempty") continue;
        $autochoice[$name[1]] = (int)$value;
    }
    if ($autochoice != null) {
        if (!isset($_POST["onlywhenempty"])) {
            $autochoice["onlyWhenEmpty"] = false;
        } else if ($_POST["onlywhenempty"] == 1) {
            $autochoice["onlyWhenEmpty"] = true;
        }
    }
    $insertstmt = $mysql->prepare("UPDATE `users` SET `autochoice`=? WHERE `id` = ?");
    if ($autochoice == null) {
        $autochoicejson = null;
    } else {
        $autochoicejson = json_encode((object)$autochoice);
    }
    $insertstmt->bind_param("si", $autochoicejson, $_SESSION["userId"]);
    if ($insertstmt->execute()) {
        Message::addMessage("Sikeres módosítás!", MessageType::success);
    }
}
if (isset($_POST["from"]) && isset($_POST["to"])) {
    autochoice($_POST["from"], $_POST["to"], [$_SESSION["userId"]]);
}
$getsettingsstmt = $mysql->prepare("SELECT `ac`.* FROM `users`, JSON_TABLE(`autochoice`, '$' COLUMNS (
	`0` INT PATH '$.\"0\"',
    `1` INT PATH '$.\"1\"',
    `2` INT PATH '$.\"2\"',
    `3` INT PATH '$.\"3\"',
    `4` INT PATH '$.\"4\"',
    `5` INT PATH '$.\"5\"',
    `6` INT PATH '$.\"6\"',
    `onlyWhenEmpty` BOOL PATH '$.\"onlyWhenEmpty\"'
)) `ac` WHERE `id` = ?;");
$getsettingsstmt->bind_param("i", $_SESSION["userId"]);
$getsettingsstmt->execute();
$settings = $getsettingsstmt->get_result()->fetch_all(MYSQLI_ASSOC);
echo $twig->render("set.autochoice.html.twig", ["settings" => $settings[0] ?? null]);
?>