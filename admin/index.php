<?php

require_once "../config.php";

authUser(1);

echo $twig->render("adminbase.html.twig");

?>