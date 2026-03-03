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
    $menustmt = $mysql->prepare("SELECT DISTINCT 1 FROM `menu` WHERE `date` = ?");
    $menustmt->bind_param("s", $date);
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
    $mysql->query("DELETE FROM `choices` WHERE `date` < CURRENT_DATE - INTERVAL ".$deletePastDataAfter);
    $mysql->close();
}

function autochoice(string $from, string $to, array $userIds): array {
    global $dbcred;
    $mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
    $mysql->query("SET NAMES utf8");
    $getsettingsstmt = $mysql->prepare("SELECT `ac`.* FROM `users`, JSON_TABLE(`autochoice`, '$' COLUMNS (
	`0` INT PATH '$.\"0\"',
    `1` INT PATH '$.\"1\"',
    `2` INT PATH '$.\"2\"',
    `3` INT PATH '$.\"3\"',
    `4` INT PATH '$.\"4\"',
    `5` INT PATH '$.\"5\"',
    `6` INT PATH '$.\"6\"'
    )) `ac` WHERE `id` = ?;");
    $getsettingsstmt->bind_param("i", $uid);
    $menustmt = $mysql->prepare("SELECT `date`, JSON_ARRAYAGG(`id`) AS `menuitems` FROM `menu` WHERE (SELECT CASE WHEN CURDATE() BETWEEN `start` AND `end` THEN TRUE ELSE FALSE END AS `fillable` FROM `deadlines` WHERE `date` BETWEEN `from` AND `to` ORDER BY `fillable` DESC) IS NOT FALSE AND `date` BETWEEN ? AND ? GROUP BY `date`");
    $menustmt->bind_param("ss", $from, $to);
    $menustmt->execute();
    $menuresult = $menustmt->get_result();
    $menu = [];
    while ($row = $menuresult->fetch_array()) {
        $menu[] = [
            "date" => new DateTime($row["date"]),
            "menu" => json_decode($row["menuitems"], true)
        ];
    }
    $mysql->begin_transaction();
    $nullstmt = $mysql->prepare("INSERT INTO `choices`(`userId`, `date`, `menuId`) VALUES (?,?,NULL) ON DUPLICATE KEY UPDATE `userId`=`userId`");
    $nullstmt->bind_param("is", $uid, $date);
    $randomchoicestmt = $mysql->prepare("INSERT INTO `choices`(`userId`, `date`, `menuId`) SELECT ?, `menu`.`date`, `id` FROM `menu` WHERE `menu`.`date` = ? ORDER BY RAND() LIMIT 1 ON DUPLICATE KEY UPDATE `userId`=`userId`");
    $randomchoicestmt->bind_param("is", $uid, $date);
    $stats = [0,0]; //[felhasználók száma, igénylések száma]
    try {
        foreach($userIds as $uid) {
            $getsettingsstmt->execute();
            $settings = $getsettingsstmt->get_result()->fetch_all(MYSQLI_ASSOC);
            if ($settings == null) {
                continue;
            }
            $settings = $settings[0];
            $haverun = false;
            foreach ($menu as $day) {
                $date = $day["date"]->format("Y-m-d");
                if ($settings[$day["date"]->format("N")-1] == false) {
                    $nullstmt->execute();
                } else if ($settings[$day["date"]->format("N")-1] == 1) {
                    $randomchoicestmt->execute();
                }
                if ($mysql->affected_rows == 1) {
                    $haverun = true;
                    $stats[1]++;
                }
            }
            if ($haverun == true) { $stats[0]++; }
        }
        $mysql->commit();
        Message::addMessage("Automata választás funkció futtatása sikeres!", MessageType::success);
        Message::addMessage("$stats[0] felhasználót és $stats[1] új igénylést érintett.", MessageType::info);
    } catch (mysqli_sql_exception $e) {
        $mysql->rollback();
        throw $e;
    }
    $mysql->close();
    return $stats;
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