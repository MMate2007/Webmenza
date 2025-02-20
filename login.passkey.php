<?php
require_once "config.php";
$WebAuthn = new lbuchs\WebAuthn\WebAuthn($rp["name"], $rp["id"]);
switch ($_GET["stage"]) {
    case 0:
        $args = $WebAuthn->getGetArgs(timeout: 60*4);
        $_SESSION["challenge"] = $WebAuthn->getChallenge();
        header('Content-Type: application/json');
        echo json_encode($args);
        break;
    case 1:
        $input = json_decode(file_get_contents('php://input'));
        $mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
        $mysql->query("SET NAMES utf8");
        $stmt = $mysql->prepare("SELECT `userId`, `publicKey` FROM `passkeys` WHERE `id` = ?");
        $id = $input->id;
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_row();
        if ($result == null) {
            throw new Exception("Credential doesn't exist!");
        }
        $WebAuthn->processGet(base64_decode($input->clientDataJSON), base64_decode($input->authenticatorData), base64_decode($input->signature), $result[1], $_SESSION["challenge"]);
        echo json_encode(["success" => true]);
        $stmt = $mysql->prepare("SELECT `groups`.`name`, `users`.`name`, `registered` FROM `users` LEFT JOIN `groups` ON `users`.`groupId` = `groups`.`id` WHERE `users`.`id` = ?");
        $stmt->bind_param("i", $result[0]);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_row();
        if ($row[0] === "_admin") $_SESSION["admin"] = 2; else if ($row[0] === "_manager") $_SESSION["admin"] = 1;
        $_SESSION["userId"] = $result[0];
        $_SESSION["name"] = $row[1];
        break;
}
?>