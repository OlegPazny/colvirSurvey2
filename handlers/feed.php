<?php
require '../vendor/autoload.php'; // Подключение PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

require_once "../assets/api/db_connect.php";

// Загрузка Excel-файла
$filePath = 'вовлеченность.xlsx';
$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray();

$columns = [];
for ($i = 1; $i <= 24; $i++) {
    ${"q$i"} = mysqli_real_escape_string($db, $_POST['q' . "$i"]);
    array_push($columns, "q" . $i);
}
$columns = implode(", ", $columns);
// Пропуск первой строки с заголовками и вставка данных
foreach ($data as $index => $row) {
    mysqli_query($db, "INSERT INTO `prev_prev_results` (" . $columns . ") VALUES ('".$row[0]."', '".$row[1]."', '".$row[2]."', '".$row[3]."', '".$row[4]."', '".$row[5]."', '".$row[6]."', '".$row[7]."', '".$row[8]."', '".$row[9]."', '".$row[10]."', '".$row[11]."', ".$row[12].", ".$row[13].", ".$row[14].", ".$row[15].", ".$row[16].", ".$row[17].", ".$row[18].", ".$row[19].", ".$row[20].", ".$row[21].", ".$row[22].", ".$row[23].")");
}
