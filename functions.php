<?php
enum MessageType: string {
    case success = "success";
    case danger = "danger";
    case warning = "warning";
    case info = "info";
    case primary = "primary";
    case secondary = "secondary";
}

class Message {
    public $content;
    public $type;
    public $dismissible;

    public function __construct(string $content, MessageType $type, bool $dismissible = true) {
        $this->content = $content;
        $this->type = $type;
        $this->dismissible = $dismissible;
    }

    public static function addMessage(string $message, MessageType $type, bool $dismissible = true): void {
        global $_SESSION;
        $_SESSION["messages"][] = new Message($message, $type, $dismissible);
    }

    public static function getMessages(): array|null {
        if (isset($_SESSION["messages"])) {
        $messages = $_SESSION["messages"];
        unset($_SESSION["messages"]);
        return $messages; }
        return null;
    }
}

function fetchDatesForCal(string|null $month = null, int|null $userId = null): array {
    global $mysql, $_SESSION;
    if ($month === null) {
        $month = date("Y-m");
    }
    if ($userId === null) {
        $userId = $_SESSION["userId"];
    }
    $start = new DateTime('first day of '.$month);
    $end = new DateTime('last day of '.$month);
    $end->modify("+1 days");
    $interval = new DateInterval('P1D');
    $days = new DatePeriod($start, $interval, $end);
    $dates = [];
    $menustmt = $mysql->prepare("SELECT DISTINCT 1 FROM `menu` WHERE `date` = ? AND `mealId` NOT IN (SELECT `mealId` FROM `excludegroupsfrommeals` INNER JOIN `users` ON `users`.`groupId` = `excludegroupsfrommeals`.`groupId` AND `users`.`id` = ?)");
    $menustmt->bind_param("si", $date, $_SESSION["userId"]);
    $choicestmt = $mysql->prepare("SELECT CASE WHEN `menuId` > 0 THEN TRUE WHEN `menuId` IS NULL THEN FALSE END FROM `choices` WHERE `userId` = ? AND `date` = ?");
    $choicestmt->bind_param("is", $userId, $date);
    foreach ($days as $day) {
        $date = $day->format("Y-m-d");
        $menustmt->execute();
        $res = $menustmt->get_result()->fetch_row();
        $res2 = null;
        if ($res != null) {
            $res = $res[0];
            $choicestmt->execute();
            $res2 = $choicestmt->get_result()->fetch_row();
            if ($res2 != null) {
                $res2 = $res2[0];
            }
        }
        $dates[$date] = [
            "menu" => $res,
            "choice" => $res2
        ];
    }
    return $dates;
}

function deletePastData(): void {
    global $dbcred, $deletePastDataAfter;
    $mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
    $mysql->query("SET NAMES utf8");
    $mysql->query("DELETE FROM `menu` WHERE `date` < CURRENT_DATE - INTERVAL ".$deletePastDataAfter);
    $mysql->query("DELETE FROM `deadlines` WHERE `to` < CURRENT_DATE - INTERVAL $deletePastDataAfter AND `end` < CURRENT_DATE - INTERVAL $deletePastDataAfter");
    $mysql->close();
}

function authUser(int $adminLevel = 0, bool $allownonregistered = false): void {
    deletePastData();
    if (!isset($_SESSION["userId"])) {
        $_SESSION["messages"][] = new Message("A tartalom megtekintéséhez azonosítás szükséges.", MessageType::danger);
        header("Location: login.php");
        exit;
        return;
    }
    if ($adminLevel != 0 && !isset($_SESSION["admin"])) {
        $_SESSION["messages"][] = new Message("A tartalom megtekintéséhez ügyintézői vagy adminisztrátori jogosultság szükséges.", MessageType::danger);
        header("Location: login.php");
        exit;
        return;
    }
    if ($adminLevel == 2 && $_SESSION["admin"] < 2) {
        $_SESSION["messages"][] = new Message("A tartalom megtekintéséhez adminisztrátori jogosultság szükséges.", MessageType::danger);
        header("Location: login.php");
        exit;
        return;
    }
    if ($adminLevel == 1 && $_SESSION["admin"] < 1) {
        $_SESSION["messages"][] = new Message("A tartalom megtekintéséhez legalább ügyintézői jogosultság szükséges.", MessageType::danger);
        header("Location: login.php");
        exit;
        return;
    }
    if (isset($_SESSION["registered"])) {
    if ($_SESSION["registered"] == false && $allownonregistered == false) {
        Message::addMessage("Első belépésnél a jelszóváltoztatás köztelező! Kérjük, hogy változtassa meg jelszavát!", MessageType::warning, false);
        header("Location: modify.password.php");
        exit;
    } }
}
?>