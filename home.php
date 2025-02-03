<?php
require_once "config.php";
authUser();
echo $twig->render("home.html.twig");
?>