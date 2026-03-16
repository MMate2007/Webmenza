<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
if (isset($_POST["from"]) && isset($_POST["to"])) {
    $usersres = $mysql->query("SELECT `users`.`id` FROM `users` LEFT JOIN `groups` ON `groupId` = `groups`.`id` WHERE `registered` = 1 AND `groups`.`name` NOT LIKE '\_%'");
    $users = array_column($usersres->fetch_all(MYSQLI_ASSOC),"id");
    if (isset($_POST["preview"])) {
        $userIds = $users;
        $ignoreDeadlines = (bool)$_POST["ignoredeadlines"] ?? false;
        $from = $_POST["from"];
        $to = $_POST["to"];
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
        $getsettingsstmt->bind_param("i", $uid);
        if ($ignoreDeadlines === false) {
            $menustmt = $mysql->prepare("SELECT `date`, JSON_ARRAYAGG(`id`) AS `menuitems` FROM `menu` WHERE (SELECT CASE WHEN CURDATE() BETWEEN `start` AND `end` THEN TRUE ELSE FALSE END AS `fillable` FROM `deadlines` WHERE `date` BETWEEN `from` AND `to` ORDER BY `fillable` DESC) IS NOT FALSE AND `date` BETWEEN ? AND ? GROUP BY `date`");
        } else if ($ignoreDeadlines === true) {
            $menustmt = $mysql->prepare("SELECT `date`, JSON_ARRAYAGG(`id`) AS `menuitems` FROM `menu` WHERE `date` BETWEEN ? AND ? GROUP BY `date`");
        }
        $menustmt->bind_param("ss", $from, $to);
        $menustmt->execute();
        $menuresult = $menustmt->get_result();
        $menu = [];
        while ($row = $menuresult->fetch_array()) {
            $menu[] = [
                "date" => new DateTime($row["date"]),
                "menu" => json_decode($row["menuitems"], true)
            ];
        }
        $mysql->begin_transaction();
        $nullstmt = $mysql->prepare("INSERT INTO `choices`(`userId`, `date`, `menuId`) VALUES (?,?,NULL) ON DUPLICATE KEY UPDATE `userId`=`userId`");
        $nullstmt->bind_param("is", $uid, $date);
        $randomchoicestmt = $mysql->prepare("INSERT INTO `choices`(`userId`, `date`, `menuId`) SELECT ?, `menu`.`date`, `id` FROM `menu` WHERE `menu`.`date` = ? ORDER BY RAND() LIMIT 1 ON DUPLICATE KEY UPDATE `userId`=`userId`");
        $randomchoicestmt->bind_param("is", $uid, $date);
        $emptystmt = $mysql->prepare("SELECT COUNT(*) AS `choiceCount` FROM `choices` WHERE `userId` = ? AND `date` BETWEEN ? AND ?");
        $emptystmt->bind_param("iss", $uid, $from, $to);
        $stats = [0,0]; //[felhasználók száma, igénylések száma]
        $modifiedDates = [];
        $usersEnabledAutochoice = [];
        foreach($userIds as $uid) {
            $getsettingsstmt->execute();
            $settings = $getsettingsstmt->get_result()->fetch_all(MYSQLI_ASSOC);
            if ($settings == null) {
                continue;
            }
            $settings = $settings[0];
            $usersEnabledAutochoice[] = $uid;
            if ($settings["onlyWhenEmpty"] == true) {
                $emptystmt->execute();
                $choiceCount = $emptystmt->get_result()->fetch_all(MYSQLI_ASSOC)[0]["choiceCount"];
                if ($choiceCount > 0) {
                    continue;
                }
            }
            $haverun = false;
            $modificaton = [];
            foreach ($menu as $day) {
                $date = $day["date"]->format("Y-m-d");
                if ($settings[$day["date"]->format("N")-1] == false) {
                    $nullstmt->execute();
                } else if ($settings[$day["date"]->format("N")-1] == 1) {
                    $randomchoicestmt->execute();
                }
                if ($mysql->affected_rows == 1) {
                    $haverun = true;
                    $stats[1]++;
                    $modificaton[] = $day["date"];
                }
            }
            if ($haverun == true) { $stats[0]++; }
            $modifiedDates[$uid] = $modificaton;
        }
        $groupsres = $mysql->query("SELECT `id`, `name` FROM `groups` WHERE `name` NOT LIKE '\_%' ORDER BY `name`");
        $stmt = $mysql->prepare("SELECT DISTINCT `date` FROM `menu` WHERE `date` BETWEEN ? AND ? ORDER BY `date`");
        $stmt->execute([$from, $to]);
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
                    $newmenu = ["choice" => $menu];
                    if (isset($modifiedDates[$userid])) {
                    if (in_array($date, $modifiedDates[$userid])) {
                        $newmenu["modified"] = true;
                    } }
                    $newuser["choiceletters"][] = $newmenu;
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
                $newmenu = ["choice" => $menu];
                if (isset($modifiedDates[$userid])) {
                if (in_array($date, $modifiedDates[$userid])) {
                    $newmenu["modified"] = true;
                } }
                $newuser["choiceletters"][] = $newmenu;
            }
            $newdata["users"][] = $newuser;
        }
        $data[] = $newdata;
        $mysql->rollback();
        echo $twig->render("preview.autochoice.html.twig", ["data" => $data, "dates" => $dates, "usersEnabledAutochoice" => $usersEnabledAutochoice, "from" => $from, "to" => $to, "ignoreDeadlines" => $ignoreDeadlines, "stats" => $stats]);
        $mysql->close();
        exit;
    } else {
        autochoice($_POST["from"], $_POST["to"], $users, $_POST["ignoredeadlines"] ?? false);
    }
}
$dates = $mysql->query("SELECT `from`, `to` FROM `deadlines` WHERE `end` <= CURRENT_DATE ORDER BY `end` DESC, `start` ASC LIMIT 1")->fetch_row();
echo $twig->render("run.autochoice.html.twig", ["from" => $dates[0] ?? null, "to" => $dates[1] ?? null]);
?>