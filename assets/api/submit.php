<?php
require_once "db_connect.php";

// Проверяем соединение
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

// Проверяем, что данные переданы через POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Собираем данные из формы
    $columns=[];
    for ($i=1; $i<=24; $i++){
        ${"q$i"} = mysqli_real_escape_string($db, $_POST['q'."$i"]);
        array_push($columns, "q".$i);
    }
    $columns=implode(", ", $columns);
    // Пример запроса для вставки данных
    $sql = "INSERT INTO results (".$columns.") VALUES ('$q1', '$q2', '$q3', '$q4', '$q5', '$q6', '$q7', '$q8', '$q9', '$q10', '$q11', '$q12', '$q13', '$q14', '$q15', '$q16', '$q17', '$q18', '$q19', '$q20', '$q21', '$q22', '$q23', '$q24')";

    if ($db->query($sql) === TRUE) {
        // Если данные успешно сохранены
        echo "Данные успешно сохранены!";
    } else {
        // Если произошла ошибка
        echo "Ошибка: " . $sql . "<br>" . $db->error;
    }

    // Закрываем соединение
    $db->close();
}
?>
