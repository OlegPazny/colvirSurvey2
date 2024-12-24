<?php
require 'db_connect.php';

try {
    // Создание бэкапа
    $backupFile = "backups/db_backup_" . date('Y-m-d_H-i-s') . ".sql";
    $command = "mysqldump -u [username] -p[password] [database_name] > $backupFile";
    system($command, $returnVar);

    if ($returnVar !== 0) {
        throw new Exception("Ошибка при создании бэкапа.");
    }

    // Начало транзакции
    $db->beginTransaction();

    // Смещение данных
    $db->exec("INSERT INTO prev_results SELECT * FROM results");
    $db->exec("TRUNCATE TABLE results");

    $db->exec("INSERT INTO prev_prev_results SELECT * FROM prev_results");
    $db->exec("TRUNCATE TABLE prev_results");

    $db->exec("TRUNCATE TABLE prev_prev_results");

    $db->commit();

    echo "Новый опрос начат. Бэкап базы данных создан.";
} catch (Exception $e) {
    $db->rollBack();
    http_response_code(500);
    echo "Ошибка: " . $e->getMessage();
}
