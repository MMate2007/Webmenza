<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
if (!isset($_GET["from"]) && !isset($_GET["to"])) {
    $_GET["from"] = date("Y-m-01");
    $_GET["to"] = date("Y-m-t");
}
$groupsres = $mysql->query("SELECT `id`, `name` FROM `groups` WHERE `name` NOT LIKE '\_%' ORDER BY `name`");
$stmt = $mysql->prepare("SELECT DISTINCT `date` FROM `menu` WHERE `date` BETWEEN ? AND ? ORDER BY `date`");
$stmt->execute([$_GET["from"], $_GET["to"]]);
$datesresult = $stmt->get_result();
$dates = [];
while ($daterow = $datesresult->fetch_array()) {
    $dates[] = date_create($daterow[0]);
}
$userstmt = $mysql->prepare("SELECT `id`, `name`, `registered` FROM `users` WHERE `groupId` = ? ORDER BY `name`");
$userstmt->bind_param("i", $groupid);
$choicestmt = $mysql->prepare("SELECT `menuId` FROM `choices` WHERE `userId` = ? AND `date` = ?");
$choicestmt->bind_param("is", $userid, $sqldate);
$data = [];
while ($grouprow = $groupsres->fetch_array()) {
    $newdata = [
        "id" => $grouprow["id"],
        "name" => $grouprow["name"],
        "users" => []
    ];
    $groupid = $grouprow["id"];
    $userstmt->execute();
    $usersres = $userstmt->get_result();
    while ($userrow = $usersres->fetch_array()) {
        $newuser = [
            "id" => $userrow["id"],
            "name" => $userrow["name"],
            "registered" => $userrow["registered"],
            "choiceletters" => []
        ];
        $userid = $userrow["id"];
        foreach ($dates as $date) {
            $sqldate = date_format($date, "Y-m-d");
            $choicestmt->execute();
            $res = $choicestmt->get_result();
            $r = $res->fetch_row();
            if ($r !== null) {
                if ($r[0] === null) {
                    $menu = "X";
                } else {
                    $menu = $menuletters[$r[0]-1];
                }
            } else {
                $menu = null;
            }
            $newuser["choiceletters"][] = $menu;
        }
        $newdata["users"][] = $newuser;
    }
    $data[] = $newdata;
}
// Nem csoportban lévő felhasználók
$grouprow = [
    "id" => null,
    "name" => "Nem csoportban lévő felhasználók"
];
    $newdata = [
        "id" => $grouprow["id"],
        "name" => $grouprow["name"],
        "users" => []
    ];
    $groupid = $grouprow["id"];
    $userstmt->execute();
    $usersres = $userstmt->get_result();
    while ($userrow = $usersres->fetch_array()) {
        $newuser = [
            "id" => $userrow["id"],
            "name" => $userrow["name"],
            "registered" => $userrow["registered"],
            "choiceletters" => []
        ];
        $userid = $userrow["id"];
        foreach ($dates as $date) {
            $sqldate = date_format($date, "Y-m-d");
            $choicestmt->execute();
            $res = $choicestmt->get_result();
            $r = $res->fetch_row();
            if ($r !== null) {
                if ($r[0] === null) {
                    $menu = "X";
                } else {
                    $menu = $menuletters[$r[0]-1];
                }
            } else {
                $menu = null;
            }
            $newuser["choiceletters"][] = $menu;
        }
        $newdata["users"][] = $newuser;
    }
    $data[] = $newdata;
echo $twig->render("list.choices.html.twig", ["data" => $data, "dates" => $dates, "from" => $_GET["from"], "to" => $_GET["to"]]);
?>