<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$nonreg = $mysql->query("SELECT COUNT(*) FROM `users` WHERE `registered` = 0")->fetch_row()[0];
$allreg = $mysql->query("SELECT COUNT(*) FROM `users`")->fetch_row()[0];
$passkeys = $mysql->query("SELECT COUNT(*) FROM `passkeys`")->fetch_row()[0];
$notis = $mysql->query("SELECT COUNT(*) FROM `notificationsubscriptions`")->fetch_row()[0];
echo $twig->render("adminhome.html.twig", ["nonreg" => $nonreg, "allreg" => $allreg, "passkeys" => $passkeys, "notis" => $notis]);
?>