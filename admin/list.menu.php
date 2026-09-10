<?php
require_once "../config.php";
authUser(1);
$mysql = new mysqli($dbcred["host"], $dbcred["username"], $dbcred["password"], $dbcred["db"]);
$mysql->query("SET NAMES utf8");
$result = $mysql->query("SELECT DISTINCT `date` FROM `menu`");
$dates = [];
while ($row = $result->fetch_array()) {
    $dates[] = $row[0];
}
$menu = [];
if ($dates != null) {
    foreach ($dates as $date) {
        $stmt = $mysql->prepare("SELECT `menu`.`id`, `description`, GROUP_CONCAT(`diets`.`name` SEPARATOR ', ') AS `diet` FROM `menu` INNER JOIN `menudiets` ON `menudiets`.`menuDate` = `menu`.`date` AND `menudiets`.`menuId` = `menu`.`id` INNER JOIN `diets` ON `menudiets`.`dietId` = `diets`.`id` WHERE `date` = ? GROUP BY `menu`.`date`, `menu`.`id`, `menu`.`description`");
        $stmt->execute([$date]);
        $menu[$date] = $stmt->get_result()->fetch_all();
    }
}
echo $twig->render("list.menu.html.twig", ["menus" => $menu]);
?>