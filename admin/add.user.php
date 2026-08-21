<?php
include("../config.php");

authUser(1);

$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$result = $mysql->query("SELECT * FROM `groups`");
while ($row = $result->fetch_assoc()) {
    $groups[] = $row;
}
$result = $mysql->query("SELECT * FROM `diets` ORDER BY `id`");
while ($row = $result->fetch_assoc()) {
    $diets[] = $row;
}
if (isset($_POST["name"])) {
    if ($_POST["password"] != $_POST["password2"]) {
        $_SESSION["messages"][] = new Message("A két jelszó nem egyezik!", MessageType::danger);
        echo $twig->render("adduser.html.twig", ["groups" => $groups ?? null, "diets" => $diets ?? null]);
        exit;
    }
    $statement = $mysql->prepare("INSERT INTO `users`(`name`, `password`, `groupId`, `registered`, `dietId`) VALUES (?,?,?,?,?)");
    $group = ($_POST["group"] == "") ? null : (int)$_POST["group"];
    $registered = $_POST["registered"] ?? 0;
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
    $statement->bind_param("ssiii", $_POST["name"], $password, $group, $registered, $_POST["diet"]);
    try {
    $statement->execute();
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() === 1062) {
            Message::addMessage("Ilyen nevű felhasználó már létezik a csoportban!", MessageType::danger);
        } else {
            throw $e;
        }
    }
    if (!$statement->errno) {
        Message::addMessage("Felhasználó sikeresen létrehozva!", MessageType::success);
    }
}
echo $twig->render("add.user.html.twig", ["groups" => $groups ?? null, "diets" => $diets ?? null]);
?>