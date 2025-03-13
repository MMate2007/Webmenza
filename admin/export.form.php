<?php
require_once "../config.php";

use PhpOffice\PhpSpreadsheet\Cell\CellAddress;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");

function generateSheetForGroup(int $groupId) {
    global $mysql, $spreadsheet, $includeRegistered, $menuletters;
    $stmt = $mysql->prepare("SELECT `name` FROM `groups` WHERE `id` = ?");
    $stmt->bind_param("i", $groupId);
    $stmt->execute();
    $groupName = $stmt->get_result()->fetch_row()[0];
    $sheet = $spreadsheet->createSheet();
    $sheet->setTitle($groupName);
    $sheet->getDefaultColumnDimension()->setWidth(0.67, "cm");
    $sheet->getDefaultRowDimension()->setRowHeight(0.70, "cm");
    $stmt = $mysql->prepare("SELECT DISTINCT `date` FROM `menu` WHERE `date` BETWEEN ? AND ?");
    $stmt->execute([$_POST["from"], $_POST["to"]]);
    $result = $stmt->get_result();
    $weekdays = ["H", "K", "Sze", "Cs", "P", "Szo", "V"];
    $weekday = 1;
    $endofweek = [];
    $dates = [];
    $days = [];
    $d = [];
    while ($row = $result->fetch_array()) {
        $date = date_create($row[0]);
        $d[] = $row[0];
        $dates[] = date_format($date, "j");
        $days[] = $weekdays[date_format($date, "N")-1];
        if (date_format($date, "N")-1 < $weekday) {
            $endofweek[] = count($days) + 1;
        }
        $weekday = date_format($date, "N")-1;
    }
    $sheet->fromArray([$dates, $days], startCell: "C1");
    $headStyles = [
        "alignment" => [
            "horizontal" => Alignment::HORIZONTAL_CENTER,
            "vertical" => Alignment::VERTICAL_CENTER
        ],
        "borders" => [
            "allBorders" => [
                "borderStyle" => Border::BORDER_THICK
            ]
        ],
        "font" => [
            "bold" => true
        ]
    ];
    $sheet->getStyle("C1:".$sheet->getHighestDataColumn()."2")->applyFromArray($headStyles);
    $sheet->setCellValue([sizeof($days)+3, 2], "Össz (".sizeof($days).")");
    $sheet->getColumnDimension($sheet->getHighestDataColumn())->setWidth(2, "cm");
    $osszcolumn = $sheet->getHighestDataColumn();
    $sheet->setCellValue([sizeof($days)+4, 2], "Aláírás");
    $sheet->getColumnDimension($sheet->getHighestDataColumn())->setWidth(5, "cm");
    $sheet->setCellValue("A2", "#");
    $sheet->setCellValue("B1", $groupName);
    $sheet->getStyle("B1")->applyFromArray([
        "alignment" => [
            "horizontal" => Alignment::HORIZONTAL_CENTER,
            "vertical" => Alignment::VERTICAL_CENTER
        ]
    ]);
    $sheet->setCellValue("B2", "Név");
    if ($includeRegistered) {
    $stmt = $mysql->prepare("SELECT `name`, `id` FROM `users` WHERE `groupId` = ? ORDER BY `name`");
    } else {
    $stmt = $mysql->prepare("SELECT `name`, `id` FROM `users` WHERE `registered` = 0 AND `groupId` = ? ORDER BY `name`");
    }
    $stmt->bind_param("i", $groupId);
    $stmt->execute();
    $result = $stmt->get_result();
    $counter = 1;
    $users = [];
    if ($includeRegistered) {
        $s = $mysql->prepare("SELECT `menuId` FROM `choices` WHERE `userId` = ? AND `date` = ?");
        $s->bind_param("is", $uid, $day);
    }
    while ($row = $result->fetch_array()) {
        $entry = [$counter, $row[0]];
        if ($includeRegistered) {
            $uid = $row[1];
            $name = $row[0];
            foreach ($d as $day) {
                $s->execute();
                $res = $s->get_result();
                $r = $res->fetch_row();
                if ($r !== null) {
                    if ($r[0] === null) {
                        $menu = "X";
                    } else {
                        $menu = $menuletters[$r[0]-1];
                    }
                    $entry[] = $menu;
                } else {
                    $entry[] = null;
                }
            }
        }
        $users[] = $entry;
        $counter++;
    }
    $sheet->fromArray($users, startCell: "A3");
    $sheet->getStyle("A2:".$sheet->getHighestDataColumn()."2")->applyFromArray($headStyles);
    $choiceStyles = [
        "alignment" => [
            "horizontal" => Alignment::HORIZONTAL_CENTER,
            "vertical" => Alignment::VERTICAL_CENTER
        ],
        "borders" => [
            "allBorders" => [
                "borderStyle" => Border::BORDER_THIN
            ]
        ]
    ];
    $sheet->getStyle("C3:".$sheet->getHighestDataColumn().$sheet->getHighestDataRow())->applyFromArray($choiceStyles);
    $nameStyles = [
        "alignment" => [
            "horizontal" => Alignment::HORIZONTAL_RIGHT,
            "vertical" => Alignment::VERTICAL_CENTER
        ],
        "borders" => [
            "allBorders" => [
                "borderStyle" => Border::BORDER_THIN
            ],
            "left" => [
                "borderStyle" => Border::BORDER_THICK
            ],
            "right" => [
                "borderStyle" => Border::BORDER_THICK
            ],
        ]
    ];
    $sheet->getStyle("B3:B".$sheet->getHighestDataRow())->applyFromArray($nameStyles);
    $noStyles = [
        "alignment" => [
            "horizontal" => Alignment::HORIZONTAL_CENTER,
            "vertical" => Alignment::VERTICAL_CENTER
        ],
        "borders" => [
            "allBorders" => [
                "borderStyle" => Border::BORDER_THIN
            ]
        ]
    ];
    $sheet->getStyle("A3:A".$sheet->getHighestDataRow())->applyFromArray($noStyles);
    $bottomStyles = [
        "borders" => [
            "bottom" => [
                "borderStyle" => Border::BORDER_THICK
            ]
        ]
    ];
    $sheet->getStyle("A".$sheet->getHighestDataRow().":".$sheet->getHighestDataColumn().$sheet->getHighestDataRow())->applyFromArray($bottomStyles);
    $leftStyles = [
        "borders" => [
            "left" => [
                "borderStyle" => Border::BORDER_THICK
            ]
        ]
    ];
    $sheet->getStyle("A2:"."A".$sheet->getHighestDataRow())->applyFromArray($leftStyles);
    $sideStyles = [
        "borders" => [
            "right" => [
                "borderStyle" => Border::BORDER_THICK
            ]
        ]
    ];
    $sheet->getStyle($sheet->getHighestDataColumn()."2:".$sheet->getHighestDataColumn().$sheet->getHighestDataRow())->applyFromArray($sideStyles);
    foreach ($endofweek as $col) {
        $sheet->getStyle([$col, 2, $col, $sheet->getHighestDataRow()])->applyFromArray($sideStyles);
    }
    $osszStyles = [
        "borders" => [
            "right" => [
                "borderStyle" => Border::BORDER_THICK
            ],
            "left" => [
                "borderStyle" => Border::BORDER_THICK
            ]
        ]
    ];
    $sheet->getStyle($osszcolumn."2:".$osszcolumn.$sheet->getHighestDataRow())->applyFromArray($osszStyles);
    $sheet->getColumnDimension("B")->setAutoSize(true);
}

if (isset($_POST["from"])) {
    $groupName = "Csoport";
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $spreadsheet->getProperties()->setCreator('Webmenza - '.$_SESSION["name"])
        ->setLastModifiedBy('Webmenza - '.$_SESSION["name"])
        ->setTitle($groupName." menza igénylőlap ".date_format(date_create($_POST["from"]), "Y. m. d.")." - ".date_format(date_create($_POST["to"]), "Y. m. d."));
    $sheetIndex = $spreadsheet->getIndex(
        $spreadsheet->getSheetByName('Worksheet')
    );
    $spreadsheet->removeSheetByIndex($sheetIndex);
    $includeRegistered = $_POST["unregistered"] ?? 0;
    if ($_POST["group"] != "") {
        generateSheetForGroup($_POST["group"]);
    } else {
        $result = $mysql->query("SELECT `id` FROM `groups` WHERE `name` NOT LIKE '\_%' ORDER BY `name`");
        while ($row = $result->fetch_array()) {
            generateSheetForGroup($row["id"]);
        }
    }
    switch ($_POST["format"]) {
        case "Xlsx":
            $format = ".xlsx";
            break;
        case "Xls":
            $format = ".xls";
            break;
        case "Ods":
            $format = ".ods";
            break;
    }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: inline;filename="'.$groupName.' igénylőlap '.date_format(date_create($_POST['from']), 'Y. m. d.').' - '.date_format(date_create($_POST['to']), 'Y. m. d').$format.'"');
    header('Cache-Control: max-age=0');
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, $_POST["format"]);
    $writer->save('php://output');
} else {
    $groups = $mysql->query("SELECT * FROM `groups`")->fetch_all(MYSQLI_ASSOC);
    $dates = $mysql->query("SELECT `from`, `to` FROM `deadlines` WHERE CURRENT_DATE BETWEEN `start` AND `end` ORDER BY `end` ASC, `start` DESC LIMIT 1")->fetch_row();
    echo $twig->render("export.choices.html.twig", ["groups" => $groups, "from" => $dates[0] ?? null, "to" => $dates[1] ?? null, "groupId" => $_GET["group"] ?? null, "form" => true]);
}
?>