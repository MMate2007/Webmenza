<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$meals = $mysql->query("SELECT `id`, `name` FROM `meals` ORDER BY `name`")->fetch_all();
echo $twig->render("list.mealtype.html.twig", ["meals" => $meals ?? null]);
?>