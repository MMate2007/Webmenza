<?php
require_once "config.php";
authUser();
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$data = null;
if (isset($_GET["from"]) && isset($_GET["to"])) {
    $stmt = $mysql->prepare("SELECT `date`, `menuId` FROM `choices` WHERE `userId` = ? AND `date` BETWEEN ? AND ? AND `menuId` IS NOT NULL ORDER BY `date`");
    $stmt->bind_param("iss", $_SESSION["userId"], $_GET["from"], $_GET["to"]);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $data = base64_encode(serialize($result));
    echo $twig->render("copycode.share.choices.html.twig", ["data" => $data]);
} else if (isset($_POST["data"]) && !isset($_POST["import"])) {
    $array = unserialize(base64_decode($_POST["data"]));
    $proposal = [];
    $namestmt = $mysql->prepare("SELECT `description` FROM `menu` WHERE `date` = ? AND `id` = ?");
    $namestmt->bind_param("si", $date, $id);
    $mystmt = $mysql->prepare("SELECT `menuId` FROM `choices` WHERE `userId` = ? AND `date` = ?");
    $mystmt->bind_param("is", $_SESSION["userId"], $date);
    foreach ($array as $entry) {
        $date = $entry["date"];
        $id = $entry["menuId"];
        $namestmt->execute();
        $name = $namestmt->get_result()->fetch_row()[0];
        $mystmt->execute();
        $mychoice = $mystmt->get_result()->fetch_array();
        if ($mychoice != null) {
            if ($mychoice["menuId"] === null) {
                $myname = "Erre a napra nem kérek étkeztetést.";
            } else {
            $id = $mychoice["menuId"];
            $namestmt->execute();
            $myname = $namestmt->get_result()->fetch_row()[0];
            }
        } else {
            $myname = null;
        }
        $proposal[$entry["date"]] = [$name, $myname];
    }
    echo $twig->render("proposal.share.choices.html.twig", ["data" => $_POST["data"], "proposal" => $proposal]);
} else if (isset($_POST["data"]) && isset($_POST["import"])) {
    $array = unserialize(base64_decode($_POST["data"]));
    $mysql->begin_transaction();
    try {
        $dates = $_POST["dates"] ?? array();
        $stmt = $mysql->prepare("INSERT INTO `choices`(`userId`, `date`, `menuId`) VALUES (?,?,?) ON DUPLICATE KEY UPDATE `menuId` = ?");
        $stmt->bind_param("isii", $_SESSION["userId"], $date, $menuId, $menuId);
        foreach ($array as $entry) {
            if (in_array($entry["date"], $dates)) {
                $date = $entry["date"];
                $menuId = $entry["menuId"];
                $stmt->execute();
            }
        }
        $mysql->commit();
        Message::addMessage("Sikeres importálás!", MessageType::success);
        echo $twig->render("forms.share.choices.html.twig");
    } catch (Exception|Throwable $e) {
        echo "halló";
        $mysql->rollback();
        Message::addMessage("Sikertelen importálás!", MessageType::danger);
        throw $e;
    }
} else {
echo $twig->render("forms.share.choices.html.twig");
}
?>