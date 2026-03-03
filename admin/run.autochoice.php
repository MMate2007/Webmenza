<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
if (isset($_POST["from"]) && isset($_POST["to"])) {
    $usersres = $mysql->query("SELECT `users`.`id` FROM `users` LEFT JOIN `groups` ON `groupId` = `groups`.`id` WHERE `registered` = 1 AND `groups`.`name` NOT LIKE '\_%'");
    $users = array_column($usersres->fetch_all(MYSQLI_ASSOC),"id");
    autochoice($_POST["from"], $_POST["to"], $users, $_POST["ignoredeadlines"] ?? false);
}
$dates = $mysql->query("SELECT `from`, `to` FROM `deadlines` WHERE `end` <= CURRENT_DATE ORDER BY `end` DESC, `start` ASC LIMIT 1")->fetch_row();
echo $twig->render("run.autochoice.html.twig", ["from" => $dates[0] ?? null, "to" => $dates[1] ?? null]);
?>