<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$result = $mysql->query("SELECT `id`, `name` FROM `groups`");
if (isset($_GET["search"])) {
    if ($_GET["search"] != null) {
        $search = $_GET["search"];
        str_replace("_", "", $search);
        $stmt = $mysql->prepare("SELECT `id`, `name` FROM `groups` WHERE `name` LIKE ?");
        $stmt->execute(["%".$search."%"]);
        $result = $stmt->get_result();
}}
while ($row = $result->fetch_array()) {
    $groups[] = $row;
}
echo $twig->render("list.group.html.twig", ["groups" => $groups ?? null, "search" => $_GET["search"] ?? null]);
?>