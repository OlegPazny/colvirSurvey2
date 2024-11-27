<?php
require '../vendor/autoload.php'; // Подключение PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\IOFactory;

require_once "../assets/api/db_connect.php";

// Загрузка Excel-файла
$filePath = 'list.xls';
$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getActiveSheet();
$data = $sheet->toArray();

foreach ($data as $index => $row) {
    if(strpos($row[8], "Внештатный")){
        continue;
    }
    $department_id=NULL;
    if($row[3]=="Департамент инноваций"){
        $department_id=1;
    }else if($row[3]=="Технический департамент"){
        $department_id=2;
    }else if($row[3]=="Производственный департамент"&&strpos($row[4], "Группы разработки")&&!strpos($row[5], "Группа выпуска документации")){
        $department_id=3;
    }else if($row[3]=="Производственный департамент"&&strpos($row[4], "Отдел тестирования")){
        $department_id=4;
    }else if($row[3]=="Производственный департамент"&&strpos($row[4], "Служба сопровождения")){
        $department_id=5;
    }else if($row[3]=="Производственный департамент"&&strpos($row[4], "Группы разработки")&&strpos($row[5], "Группа выпуска документации")){
        $department_id=6;
    }else if($row[3]=="Производственный департамент"&&!strpos($row[4], "Отдел тестирования")&&!strpos($row[4], "Служба сопровождения")&&!strpos($row[4], "Группы разработки")&&!strpos($row[5], "Группа выпуска документации")){
        $department_id=7;
    }else if($row[3]=="Департамент развития бизнеса и продуктов"&&strpos($row[7], "Аккаунт-менеджер")){
        $department_id=8;
    }else if($row[3]=="Департамент развития бизнеса и продуктов"&&strpos($row[7], "Пресейл консультант")){
        $department_id=9;
    }else if($row[3]=="Департамент развития бизнеса и продуктов"&&!strpos($row[7], "Пресейл консультант")&&!strpos($row[7], "Аккаунт-менеджер")){
        $department_id=10;
    }else if($row[3]=="Департамент бизнес-анализа"){
        $department_id=11;
    }else if($row[3]=="Проектный офис"&&strpos($row[7], "Менеджер разработки")){
        $department_id=12;
    }else if($row[3]=="Проектный офис"&&strpos($row[7], "Менеджер внедрения")){
        $department_id=13;
    }else if($row[3]=="Проектный офис"&&strpos($row[4], "Отдел внедрения")){
        $department_id=14;
    }else if($row[3]=="Служба персонала"){
        $department_id=15;
    }else if($row[3]=="Служба бизнес-процессов"){
        $department_id=16;
    }else if($row[3]=="Бэк-офис"){
        $department_id=17;
    }
    if(strpos($row[7], "Руководитель")){
        $department_id=18;
    }
    if($department_id!=NULL){
        mysqli_query($db, "INSERT INTO `employees` VALUES (NULL, $department_id)");
    }
}
?>
