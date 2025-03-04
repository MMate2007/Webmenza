<?php

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
if (isset($_POST["start"])) {
    $query = $mysql->prepare("INSERT INTO `deadlines`(`from`, `to`, `start`, `end`) VALUES (?,?,?,?)");
    $query->execute([$_POST["from"], $_POST["to"], $_POST["start"], $_POST["end"]]);
    Message::addMessage("Határidő hozzáadása sikeres!", MessageType::success);
    if (isset($_POST["notifyusers"])) {
        if ($_POST["notifyusers"] == 1) {
            $payload = json_encode([
                "title" => "Új határidő",
                "body" => "A ".date_format(date_create($_POST["from"]), "m. d.")." - ".date_format(date_create($_POST["to"]), "m. d.")." szakasz kitöltési határideje: ".date_format(date_create($_POST["end"]), "Y. m. d."),
                "url" => "set.menu.php?date=".$_POST["from"]
            ]);
            $webpush = new WebPush(["VAPID" => $vapid]);
            $result = $mysql->query("SELECT `data` FROM `notificationsubscriptions`");
            while ($row = $result->fetch_array()) {
                $subscription = Subscription::create(json_decode($row[0], true));
                $webpush->queueNotification($subscription, $payload);
            }
            $stmt = $mysql->prepare("DELETE FROM `notificationsubscriptions` WHERE `data`->'$.\"endpoint\"' = ?");
            $stmt->bind_param("s", $endpoint);
            foreach ($webpush->flush() as $result) {
                if (!$result->isSuccess()) {
                    if ($result->isSubscriptionExpired()) {
                        $endpoint = $result->getEndpoint();
                        $stmt->execute();
                    } else {
                    Message::addMessage("Néhány értesítés kiküldése sikertelen!", MessageType::danger);
                    }
                }
            }
        }
    }
}
echo $twig->render("add.deadline.html.twig");
?>