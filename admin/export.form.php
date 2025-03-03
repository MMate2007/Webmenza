<?php
require_once "../config.php";

use PhpOffice\PhpSpreadsheet\Cell\CellAddress;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");

function generateSheetForGroup(int $groupId) {
    global $mysql, $spreadsheet;
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
    $sheet->setCellValue([sizeof($days)+3, 2], "Össz (".sizeof($days).")");
    $sheet->getColumnDimension($sheet->getHighestDataColumn())->setWidth(2, "cm");
    $osszcolumn = $sheet->getHighestDataColumn();
    $sheet->setCellValue([sizeof($days)+4, 2], "Aláírás");
    $sheet->getColumnDimension($sheet->getHighestDataColumn())->setWidth(5, "cm");
    $sheet->setCellValue("A2", "#");
    $sheet->setCellValue("B2", "Név");
    $stmt = $mysql->prepare("SELECT `name` FROM `users` WHERE `registered` = 0 AND `groupId` = ? ORDER BY `name`");
    $stmt->bind_param("i", $groupId);
    $stmt->execute();
    $result = $stmt->get_result();
    $counter = 1;
    while ($row = $result->fetch_array()) {
        $users[] = [$counter, $row[0]];
        $counter++;
    }
    $sheet->fromArray($users, startCell: "A3");
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
    $sheet->getStyle("A2:".$sheet->getHighestDataColumn()."2")->applyFromArray($headStyles);
    $choiceStyles = [
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
    echo $twig->render("export.choices.html.twig", ["groups" => $groups, "from" => $_GET["from"] ?? null, "to" => $_GET["to"] ?? null, "groupId" => $_GET["group"] ?? null]);
}
?>