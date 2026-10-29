<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$result = $mysql->query("SELECT * FROM `diets` ORDER BY `name`");
while ($row = $result->fetch_array()) {
    $diets[] = $row;
}
echo $twig->render("list.diet.html.twig", ["diets" => $diets ?? null]);
?>