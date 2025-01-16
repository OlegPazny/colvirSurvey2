<?php
require_once "db_connect.php";

// Устанавливаем заголовки для JSON-ответа
header('Content-Type: application/json; charset=utf-8');

$response = [
    "status" => "error", // Статус по умолчанию
    "message" => "Неизвестная ошибка", // Сообщение по умолчанию
];

try {
    // Проверяем соединение
    if ($db->connect_error) {
        throw new Exception("Ошибка подключения к базе данных: " . $db->connect_error);
    }

    // Проверяем, что данные переданы через POST
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Собираем комментарий
        $comment = (!empty($_POST['comment']) && trim($_POST['comment']) !== "") ? trim($_POST['comment']) : null;

        // Собираем ответы на вопросы
        $answers = [];
        for ($i = 1; $i <= 24; $i++) {
            $questionKey = "q" . $i;
            if (isset($_POST[$questionKey]) && strtolower(trim($_POST[$questionKey])) !== "null") {
                $answers[$questionKey] = trim($_POST[$questionKey]);
            } else {
                $answers[$questionKey] = null; // Устанавливаем null для пустых или "null"
            }
        }

        // Проверяем, что данные заполнены
        if (!empty($comment) || array_filter($answers)) {
            // Используем транзакцию для атомарности
            $db->begin_transaction();
            try {
                // Записываем комментарий в таблицу comments
                if (!empty($comment)) {
                    $commentSql = "INSERT INTO comments (comment) VALUES (?)";
                    $commentStmt = $db->prepare($commentSql);
                    $commentStmt->bind_param("s", $comment);

                    if (!$commentStmt->execute()) {
                        throw new Exception("Ошибка записи комментария: " . $commentStmt->error);
                    }

                    $commentStmt->close();
                }

                // Записываем ответы в таблицу results
                $columns = implode(", ", array_keys($answers));
                $placeholders = implode(", ", array_fill(0, count($answers), "?"));
                $resultSql = "INSERT INTO results ($columns) VALUES ($placeholders)";
                $resultStmt = $db->prepare($resultSql);

                $types = str_repeat("s", count($answers)); // Все параметры строки (s), адаптируйте под типы данных
                $params = array_values($answers);
                $resultStmt->bind_param($types, ...$params);

                if (!$resultStmt->execute()) {
                    throw new Exception("Ошибка записи результатов: " . $resultStmt->error);
                }

                $resultStmt->close();

                // Подтверждаем транзакцию
                $db->commit();

                $response["status"] = "success";
                $response["message"] = "Данные успешно сохранены!";
            } catch (Exception $e) {
                // Откатываем транзакцию в случае ошибки
                $db->rollback();
                $response["message"] = $e->getMessage();
            }
        } else {
            $response["message"] = "Пожалуйста, заполните хотя бы одно поле.";
        }
    } else {
        $response["message"] = "Неверный метод запроса.";
    }
} catch (Exception $e) {
    $response["message"] = $e->getMessage();
} finally {
    // Закрываем соединение
    $db->close();
    // Отправляем JSON-ответ
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
}
