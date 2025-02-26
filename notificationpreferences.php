<?php
require_once "config.php";
authUser();
echo $twig->render("notificationpreferences.html.twig", ["applicationServerKey" => $vapid["publicKey"]]);
?>