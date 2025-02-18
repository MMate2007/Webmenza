<?php
declare(strict_types=1);
require_once "config.php";
authUser();
use Webauthn\PublicKeyCredentialRpEntity;
$rpEntity = PublicKeyCredentialRpEntity::create(
    $rp["name"],
    $rp["id"]
);
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AttestationStatement\NoneAttestationStatementSupport;
$attestationStatementSupportManager = AttestationStatementSupportManager::create();
$attestationStatementSupportManager->add(NoneAttestationStatementSupport::create());
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\AuthenticatorAttestationResponseValidator;
$csmFactory = new CeremonyStepManagerFactory();
$creationCSM = $csmFactory->creationCeremony();
$authenticatorAttestationResponseValidator = AuthenticatorAttestationResponseValidator::create(
    $creationCSM
);
use Webauthn\PublicKeyCredentialUserEntity;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
$factory = new WebauthnSerializerFactory($attestationStatementSupportManager);
$serializer = $factory->create();
use Webauthn\PublicKeyCredentialCreationOptions;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Webauthn\PublicKeyCredential;
use Webauthn\AuthenticatorAttestationResponse;

switch($_GET["stage"]) {
    case 0:
        $userEntity = PublicKeyCredentialUserEntity::create(
            $_SESSION["name"],
            (string)$_SESSION["userId"],
            $_SESSION["name"]
        );
        $challenge = random_bytes(16);
        $publicKeyCredentialCreationOptions =
            PublicKeyCredentialCreationOptions::create(
            $rpEntity,
            $userEntity,
            $challenge
        )
        ;

        $_SESSION["pbKeyCredCreationOptions"] = $publicKeyCredentialCreationOptions;
        $jsonObject = $serializer->serialize(
            $publicKeyCredentialCreationOptions,
            'json',
            [
                AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
                JsonEncode::OPTIONS => JSON_THROW_ON_ERROR,
            ]
        );
        echo $jsonObject;        
        break;
    case 1:
        $publicKeyCredential = $serializer->deserialize(
            file_get_contents('php://input'),
            PublicKeyCredential::class,
            'json'
        );
        if (!$publicKeyCredential->response instanceof AuthenticatorAttestationResponse) {
            throw new Exception("publicKeyCredential is invalid!");
        }
        $publicKeyCredentialSource = $authenticatorAttestationResponseValidator->check(
            $publicKeyCredential->response,
            $_SESSION["pbKeyCredCreationOptions"],
            $rp["id"]
        );
        $mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
        $mysql->query("SET NAMES utf8");
        $stmt = $mysql->prepare("INSERT INTO `passkeys`(`id`, `userId`, `publicKey`, `name`) VALUES (?,?,?,?)");
        $name = "teszt";
        $id = base64_encode($publicKeyCredentialSource->publicKeyCredentialId);
        $key = base64_encode($publicKeyCredentialSource->credentialPublicKey);
        $stmt->bind_param("siss", $id, $_SESSION["userId"], $key, $name);
        $stmt->execute();
        echo json_encode(["verified" => true]);
        break;
}
?>