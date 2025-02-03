<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$result = $mysql->query("SELECT `users`.`id`, `users`.`name`, `groups`.`name` FROM `users` LEFT JOIN `groups` ON `users`.`groupId` = `groups`.`id`");
if (isset($_GET["search"])) {
    if ($_GET["search"] != null) {
        $stmt = $mysql->prepare("SELECT `users`.`id`, `users`.`name`, `groups`.`name` FROM `users` LEFT JOIN `groups` ON `users`.`groupId` = `groups`.`id` WHERE `users`.`name` LIKE ?");
        $stmt->execute(["%".$_GET["search"]."%"]);
        $result = $stmt->get_result();
}}
while ($row = $result->fetch_array()) {
    $users[] = $row;
}
echo $twig->render("list.user.html.twig", ["users" => $users ?? null, "search" => $_GET["search"] ?? null]);
?>