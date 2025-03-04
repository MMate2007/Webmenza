<?php
require_once "config.php";
authUser();
$WebAuthn = new lbuchs\WebAuthn\WebAuthn($rp["name"], $rp["id"], useBase64UrlEncoding: false);
switch ($_GET["stage"]) {
    case 0:
        $mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
        $mysql->query("SET NAMES utf8");
        $stmt = $mysql->prepare("SELECT `id` FROM `passkeys` WHERE `userId` = ?");
        $stmt->bind_param("i", $_SESSION["userId"]);
        $stmt->execute();
        $result = $stmt->get_result();
        $exclude = [];
        while ($row = $result->fetch_row()) {
            $exclude[] = base64_decode($row[0]);
        }
        $args = $WebAuthn->getCreateArgs((string)$_SESSION["userId"], $_SESSION["name"], $_SESSION["name"], requireResidentKey: true, excludeCredentialIds: $exclude);
        $_SESSION["challenge"] = $WebAuthn->getChallenge();
        header('Content-Type: application/json');
        echo json_encode($args);
        break;
    case 1:
        $input = json_decode(file_get_contents('php://input'));
        $data = $WebAuthn->processCreate(base64_decode($input->clientDataJSON), base64_decode($input->attestationObject), $_SESSION["challenge"]);
        $mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
        $mysql->query("SET NAMES utf8");
        $stmt = $mysql->prepare("INSERT INTO `passkeys`(`id`, `userId`, `publicKey`, `name`) VALUES (?,?,?,?)");
        $id = base64_encode($data->credentialId);
        $name = date("Y. m. d. H:i");
        $stmt->bind_param("siss", $id, $_SESSION["userId"], $data->credentialPublicKey, $name);
        $stmt->execute();
        echo json_encode(["success" => true]);
        break;
}
?>