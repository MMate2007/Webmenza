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
$usersstmt = $mysql->prepare("SELECT COUNT(u.`id`) AS expectedChoices FROM `deadlines` d LEFT JOIN (SELECT DISTINCT m.`date`, md.`dietId` FROM `menu` m JOIN `menudiets` md ON md.`menuDate` = m.`date` AND md.`menuId`   = m.`id`) av ON av.`date` BETWEEN d.`from` AND d.`to` LEFT JOIN (SELECT us.`id`, us.`dietId` FROM `users` us LEFT JOIN `groups` g ON us.`groupId` = g.`id` WHERE us.`registered` = TRUE AND (g.`name` NOT LIKE '\_%' OR us.`groupId` IS NULL)) u ON u.`dietId` = av.`dietId` WHERE d.`id` = ? GROUP BY d.`id`");
$usersstmt->bind_param("i", $_GET["id"]);
$usersstmt->execute();
$expectedchoices = $usersstmt->get_result()->fetch_row()[0];
$stmt = $mysql->prepare("SELECT COUNT(*) FROM `choices` INNER JOIN `deadlines` ON `date` BETWEEN `from` AND `to` WHERE `id` = ?");
$stmt->bind_param("i", $_GET["id"]);
$stmt->execute();
$choices = $stmt->get_result()->fetch_row()[0];
$stmt = $mysql->prepare("SELECT `groups`.`name` AS `groupName`, `groups`.`id` AS `groupId`, COUNT(`choices`.`date`) AS `choicesCount` FROM `choices` RIGHT JOIN `users` ON `users`.`id` = `choices`.`userId` AND `users`.`registered` = 1 RIGHT JOIN `groups` ON `groups`.`id` = `users`.`groupId` WHERE ((`date` BETWEEN (SELECT `from` FROM `deadlines` WHERE `deadlines`.`id` = ?) AND (SELECT `to` FROM `deadlines` WHERE `deadlines`.`id` = ?)) OR `date` IS NULL) AND `groups`.`name` NOT LIKE '\_%' GROUP BY `groups`.`id` ORDER BY `groups`.`name`");
$stmt->bind_param("ii", $_GET["id"], $_GET["id"]);
$stmt->execute();
$groupchoicesres = $stmt->get_result();
$groupExpectedChoicesCountStmt = $mysql->prepare("SELECT `groups`.`id` AS `groupId`, COUNT(`m`.`menuDate`) AS `count` FROM `groups` LEFT JOIN `users` ON `users`.`groupId` = `groups`.`id` AND `users`.`registered` = 1 LEFT JOIN (SELECT DISTINCT `menuDate`, `menudiets`.`dietId` FROM `menudiets` INNER JOIN `deadlines` ON `menudiets`.`menuDate` BETWEEN `deadlines`.`from` AND `deadlines`.`to` WHERE `deadlines`.`id` = ?) m ON `users`.`dietId` = `m`.`dietId` WHERE `groups`.`name` NOT LIKE '\_%' GROUP BY `groups`.`id`");
$groupExpectedChoicesCountStmt->bind_param("i", $_GET["id"]);
$groupExpectedChoicesCountStmt->execute();
$groupExpectedChoicesCountRes = $groupExpectedChoicesCountStmt->get_result();
while ($row = mysqli_fetch_array($groupExpectedChoicesCountRes)) {
    $groupExpectedChoicesCount[$row["groupId"]] = $row["count"];
}
while ($row = $groupchoicesres->fetch_array()) {
    $groupchoices[] = [
        "groupName" => $row["groupName"],
        "choicesCount" => $row["choicesCount"],
        "groupExpectedChoicesCount" => $groupExpectedChoicesCount[$row["groupId"]]
    ];
}
$groupchoicessort = function ($a, $b) {
    $apercentage = $a["choicesCount"] / ($a["groupExpectedChoicesCount"] ? $a["groupExpectedChoicesCount"] : 1);
    $bpercentage = $b["choicesCount"] / ($b["groupExpectedChoicesCount"] ? $b["groupExpectedChoicesCount"] : 1);
    if ($apercentage == $bpercentage) {
        return 0;
    }
    return ($apercentage < $bpercentage) ? -1 : 1;
};
usort($groupchoices, $groupchoicessort);
echo $twig->render("view.deadline.html.twig", ["from" => $data[0], "to" => $data[1], "start" => $data[2], "end" => $data[3], "days" => $days, "expectedchoices" => $expectedchoices, "choices" => $choices, "groupchoices" => $groupchoices]);
?>