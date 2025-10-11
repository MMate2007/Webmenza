<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$stmt = $mysql->prepare("SELECT `from`, `to`, `start`, `end` FROM `deadlines` WHERE `id` = ?");
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
$data = $stmt->get_result()->fetch_row();
$stmt = $mysql->prepare("SELECT COUNT(DISTINCT `date`) FROM `menu` INNER JOIN `deadlines` ON `date` BETWEEN `from` AND `to` WHERE `deadlines`.`id` = ?");
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
$days = $stmt->get_result()->fetch_row()[0];
$users = $mysql->query("SELECT COUNT(*) FROM `users` LEFT JOIN `groups` ON `groupId` = `groups`.`id` WHERE `registered` = 1 AND `groups`.`name` NOT LIKE '\_%'")->fetch_row()[0];
$stmt = $mysql->prepare("SELECT COUNT(*) FROM `choices` INNER JOIN `deadlines` ON `date` BETWEEN `from` AND `to` WHERE `id` = ?");
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
$choices = $stmt->get_result()->fetch_row()[0];
$stmt = $mysql->prepare("SELECT `groups`.`name` AS `groupName`, `groups`.`id` AS `groupId`, COUNT(`choices`.`date`) AS `choicesCount` FROM `choices` RIGHT JOIN `users` ON `users`.`id` = `choices`.`userId` RIGHT JOIN `groups` ON `groups`.`id` = `users`.`groupId` WHERE ((`date` BETWEEN (SELECT `from` FROM `deadlines` WHERE `deadlines`.`id` = ?) AND (SELECT `to` FROM `deadlines` WHERE `deadlines`.`id` = ?)) OR `date` IS NULL) AND `registered` = 1 AND `groups`.`name` NOT LIKE '\_%' GROUP BY `groups`.`id` ORDER BY `groups`.`name`");
$stmt->bind_param("ii", $_GET["id"], $_GET["id"]);
$stmt->execute();
$groupchoicesres = $stmt->get_result();
$groupmemberscountres = $mysql->query("SELECT `groupId`, COUNT(`id`) AS `count` FROM `users` GROUP BY `groupId`");
while ($row = mysqli_fetch_array($groupmemberscountres)) {
    $groupmemberscount[$row["groupId"]] = $row["count"];
}
while ($row = $groupchoicesres->fetch_array()) {
    $groupchoices[] = [
        "groupName" => $row["groupName"],
        "choicesCount" => $row["choicesCount"],
        "groupMembersCount" => $groupmemberscount[$row["groupId"]]
    ];
}
$groupchoicessort = function ($a, $b) {
    $apercentage = $a["choicesCount"] / ($a["groupMembersCount"] ? $a["groupMembersCount"] : 1);
    $bpercentage = $b["choicesCount"] / ($b["groupMembersCount"] ? $b["groupMembersCount"] : 1);
    if ($apercentage == $bpercentage) {
        return 0;
    }
    return ($apercentage < $bpercentage) ? -1 : 1;
};
usort($groupchoices, $groupchoicessort);
echo $twig->render("view.deadline.html.twig", ["from" => $data[0], "to" => $data[1], "start" => $data[2], "end" => $data[3], "days" => $days, "users" => $users, "choices" => $choices, "groupchoices" => $groupchoices]);
?>