$(document).ready(function() {
    // Отправка данных формы
    $('#surveyForm').submit(function(event) {
        event.preventDefault(); // Предотвращаем стандартную отправку формы

        // Сериализуем данные формы
        var formData = $(this).serialize();

        $.ajax({
            url: 'assets/api/submit.php', // Указываем путь к PHP-скрипту, который будет сохранять данные
            type: 'POST',
            data: formData,
            success: function(response) {
                // Обработка успешного ответа
                alert('Данные успешно сохранены!');
            },
            error: function(xhr, status, error) {
                // Обработка ошибок
                console.error('Ошибка: ' + error);
                alert('Произошла ошибка при сохранении данных.');
            }
        });
    });
});
