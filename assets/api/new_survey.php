<?php
require 'db_connect.php';
ob_start();

function downloadBackup() {
    try {
        header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename="backup.sql"');
    $username="root";
    $password="";
    $database = 'survey2';
    $command = "mysqldump --user=$username --password=$password $database";
    $output = shell_exec($command);

    if ($output === null) {
        echo "Ошибка выполнения mysqldump";
    } else {
        echo $output;
    }
        exit; // Завершаем выполнение скрипта
    } catch (Exception $e) {
        http_response_code(500);
        echo "Ошибка: " . $e->getMessage();
        exit; // Обязательно завершаем скрипт
    }
}

function updateTables($db) {
    try {
        // Начинаем транзакцию
        $db->autocommit(false);

        $db->query("TRUNCATE TABLE `prev_prev_results`");
        $db->query("INSERT INTO `prev_prev_results` SELECT * FROM `prev_results`");
        $db->query("TRUNCATE TABLE `prev_results`");
        $db->query("INSERT INTO `prev_results` SELECT * FROM `results`");
        $db->query("TRUNCATE TABLE `results`");

        // Подтверждаем изменения
        $db->commit();
        $db->autocommit(true);
    } catch (Exception $e) {
        // Откатываем изменения в случае ошибки
        $db->rollback();
        $db->autocommit(true);
        throw $e;
    }
}

// Проверяем, что запрошено действие
if (isset($_GET['action']) && $_GET['action'] === 'backup') {
    downloadBackup();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Выполняем сдвиг таблиц
        updateTables($db);

        echo json_encode([
            'success' => true,
            'message' => 'Новый опрос начат. Бэкап базы данных создан.',
        ]);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
        exit;
    }
}
ob_end_clean(); // Убираем весь случайный вывод
