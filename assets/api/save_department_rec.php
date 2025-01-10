<?php
require_once "db_connect.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $departmentIds = $_POST['department_ids'];
    $conclusion = $_POST['conclusion'];
    $recommendations = $_POST['recommendations'];

    $query = $db->prepare("INSERT INTO recommendations (department_ids, conclusion, recommendation) 
                             VALUES (?, ?, ?) 
                             ON DUPLICATE KEY UPDATE
                             conclusion = VALUES(conclusion),
                             recommendation = VALUES(recommendation)");

    if (!$query) {
        http_response_code(500);
        echo json_encode(['message' => 'Ошибка подготовки запроса: ' . $db->error]);
        exit;
    }

    $query->bind_param("sss", $departmentIds, $conclusion, $recommendations);

    if (!$query->execute()) {
        http_response_code(500);
        echo json_encode(['message' => 'Ошибка выполнения запроса: ' . $query->error]);
        exit;
    }

    echo json_encode(['message' => 'Данные успешно сохранены']);
    $db->close();
}
?>
