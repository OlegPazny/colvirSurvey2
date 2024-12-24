<?php
require '../../vendor/autoload.php'; // Подключение PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;
require_once "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Сохранение названия исследования
        $title = $_POST['surveyTitle'];
        mysqli_query($db, "TRUNCATE TABLE `survey`");
        $stmt = $db->prepare("INSERT INTO survey (title) VALUES (?)");
        $stmt->bind_param("s", $title);
        $stmt->execute();

        // Проверяем, загружен ли файл
        if (isset($_FILES['excelFile'])) {
            $fileTmpPath = $_FILES['excelFile']['tmp_name'];

            if (is_uploaded_file($fileTmpPath)) {
                // Загружаем файл в PhpSpreadsheet
                $spreadsheet = IOFactory::load($fileTmpPath);
                $sheet = $spreadsheet->getActiveSheet();
                $data = $sheet->toArray();
                mysqli_query($db, "TRUNCATE TABLE `employees`");
                foreach ($data as $index => $row) {
                    if (strpos($row[8], "Внештатный")!==false) {
                        continue;
                    }
                    $department_id=NULL;
                    if($row[3]=="Департамент инноваций"){
                        $department_id=1;
                    }else if($row[3]=="Технический департамент"){
                        $department_id=2;
                    }else if($row[3]=="Производственный департамент"&&strpos($row[4], "Группы разработки")!==false&&strpos($row[5], "Группа выпуска документации")===false){
                        $department_id=3;
                    }else if($row[3]=="Производственный департамент"&&strpos($row[4], "Отдел тестирования")!==false){
                        $department_id=4;
                    }else if($row[3]=="Производственный департамент"&&(strpos($row[4], "Служба сопровождения")!==false||(strpos($row[5], "Служба сопровождения")!==false))){
                        $department_id=5;
                    }else if($row[3]=="Производственный департамент"&&strpos($row[4], "Группы разработки")!==false&&strpos($row[5], "Группа выпуска документации")!==false){
                        $department_id=6;
                    }else if($row[3]=="Производственный департамент"&&strpos($row[4], "Отдел тестирования")===false&&strpos($row[4], "Служба сопровождения")===false&&strpos($row[4], "Группы разработки")===false&&strpos($row[5], "Группа выпуска документации")===false){
                        $department_id=7;
                    }else if($row[3]=="Департамент развития бизнеса и продуктов"&&strpos($row[7], "Аккаунт-менеджер")!==false){
                        $department_id=8;
                    }else if($row[3]=="Департамент развития бизнеса и продуктов"&&strpos($row[7], "Пресейл консультант")!==false){
                        $department_id=9;
                    }else if($row[3]=="Департамент развития бизнеса и продуктов"&&strpos($row[7], "Пресейл консультант")===false&&strpos($row[7], "Аккаунт-менеджер")===false){
                        $department_id=10;
                    }else if($row[3]=="Департамент бизнес-анализа"){
                        $department_id=11;
                    }else if($row[3]=="Проектный офис"&&strpos($row[7], "Менеджер разработки")!==false){
                        $department_id=12;
                    }else if($row[3]=="Проектный офис"&&strpos($row[7], "Менеджер внедрения")!==false){
                        $department_id=13;
                    }else if($row[3]=="Проектный офис"&&strpos($row[4], "Отдел внедрения")!==false){
                        $department_id=14;
                    }else if($row[3]=="Служба персонала"){
                        $department_id=15;
                    }else if($row[3]=="Служба бизнес-процессов"){
                        $department_id=16;
                    }else if($row[3]=="Бэк-офис"){
                        $department_id=17;
                    }
                    if(strpos($row[7], "Руководитель")!==false){
                        $department_id=18;
                    }
                    if ($department_id !== null) {    
                        $stmt = $db->prepare("INSERT INTO employees (id, department_id) VALUES (NULL, ?)");
                        $stmt->bind_param("i", $department_id);
                        $stmt->execute();
                    }
                }

                echo "Данные успешно обработаны!";
            } else {
                throw new Exception("Файл не загружен корректно.");
            }
        } else {
            throw new Exception("Файл не передан.");
        }
    } catch (Exception $e) {
        echo "Ошибка: " . $e->getMessage();
    }
}
?>