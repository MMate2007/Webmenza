<?php
require_once "vendor/autoload.php";
require_once "functions.php";

$rootdir = "/var/www/html/";

$dbcred = [
    "host" => "db",
    "username" => "root",
    "password" => "root",
    "db" => "webmenza"
];

$rp = [
    "name" => "localhost Webmenza",
    "id" => "localhost"
];

$vapid = [
    "subject" => $rp["id"],
    "publicKey" => "XXXXXXXXXXXXXXX",
    "privateKey" => "XXXX"
];

$debug = true;

$deletePastDataAfter = "2 MONTH";

$menuletters = "ABCDEFGHIJKL";

session_start();

$loader = new \Twig\Loader\FilesystemLoader($rootdir.'templates');
$twig = new \Twig\Environment($loader, ["debug" => $debug ?? false, "cache" => $cacheDir ?? $rootdir."cache"]);
if ($debug) $twig->addExtension(new \Twig\Extension\DebugExtension());
$twig->addFunction(new \Twig\TwigFunction("getMessages", ["Message", "getMessages"]));
$twig->addFilter(new \Twig\TwigFilter('menuletter', function ($string) {
    global $menuletters;
    if ($string == null) {
        return "-";
    }
    return $menuletters[$string-1];
}));
?>