<?php
require '../vendor/autoload.php'; // Подключение PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

require_once "../assets/api/db_connect.php";

// Загрузка Excel-файла
$filePath = 'santa.xls';
$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray();

foreach ($data as $index => $row) {
    if($row[10]==""){
        continue;
    }
    $email=trim($row[10]);
    mysqli_query($db, "INSERT INTO `ods` VALUES (NULL, '$row[1]', '$email', '$row[8]', NULL, NULL, NULL, '$row[3]', 5, NULL)");
}
?>
