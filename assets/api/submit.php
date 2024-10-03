<?php
// Подключение к базе данных
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "survey_db";

// Создаем соединение
$conn = new mysqli($servername, $username, $password, $dbname);

// Проверяем соединение
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Проверяем, что данные переданы через POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Собираем данные из формы
    $q1 = mysqli_real_escape_string($conn, $_POST['q1']); // Пример для select
    $q3 = mysqli_real_escape_string($conn, $_POST['q3']); // Пример для радиокнопок
    $q12 = mysqli_real_escape_string($conn, $_POST['q12']); // Пример для шкалы оценки

    // Добавьте сюда все остальные вопросы формы

    // Пример запроса для вставки данных
    $sql = "INSERT INTO survey_results (q1, q3, q12) VALUES ('$q1', '$q3', '$q12')";

    if ($conn->query($sql) === TRUE) {
        // Если данные успешно сохранены
        echo "Данные успешно сохранены!";
    } else {
        // Если произошла ошибка
        echo "Ошибка: " . $sql . "<br>" . $conn->error;
    }

    // Закрываем соединение
    $conn->close();
}
?>
