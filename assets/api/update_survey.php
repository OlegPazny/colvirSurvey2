<?php
require '../../vendor/autoload.php'; // Подключение PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;
require_once "db_connect.php";
$response = ['success' => false];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Сохранение названия исследования
        $title = $_POST['surveyTitle'];
        mysqli_query($db, "TRUNCATE TABLE `survey`");
        $stmt = $db->prepare("INSERT INTO survey (title_current) VALUES (?)");
        $stmt->bind_param("s", $title);
        $stmt->execute();
        unset($_POST['surveyTitle']);

        foreach($_POST as $key => $value) {
            $key = intval($key); // Преобразуем ключ в число (id департамента)
            $value = intval($value); // Преобразуем значение в число (количество сотрудников)
            $query = "UPDATE `departments` SET `current` = $value WHERE `id` = $key";
            mysqli_query($db, $query);
        }
        $response['success'] = true;
    } catch (Exception $e) {
        echo "Ошибка: " . $e->getMessage();
    }
} else {
    $response['error'] = 'Некорректный метод запроса.';
}
header('Content-Type: application/json');
echo json_encode($response);
?>