$(document).ready(function() {
    // Ограничение на длину комментария
    const MAX_COMMENT_LENGTH = 65535;  // Максимальный размер поля TEXT в MySQL

    // Отправка данных формы
    $('#surveyForm').submit(function(event) {
        event.preventDefault(); // Предотвращаем стандартную отправку формы

        // Получаем комментарий
        var comment = $('#comment').val();

        // Проверяем длину комментария
        if (comment.length > MAX_COMMENT_LENGTH) {
            alert("Комментарий слишком длинный! Максимальная длина: " + MAX_COMMENT_LENGTH + " символов.");
            return; // Прерываем отправку формы
        }

        // Сериализуем данные формы
        var formData = $(this).serialize();

        $.ajax({
            url: 'assets/api/submit.php', // Указываем путь к PHP-скрипту, который будет сохранять данные
            type: 'POST',
            data: formData,
            success: function(response) {
                alert(response.message); // Показываем сообщение об успешной отправке

                // Очищаем форму после успешной отправки
                $('#surveyForm')[0].reset();  // Очистка всех полей формы

                // Если вам нужно очистить только комментарий
                // $('#comment').val(''); // Для очистки только поля комментария
            },
            error: function(xhr, status, error) {
                // Обработка ошибок
                console.error('Ошибка: ' + error);
                alert('Произошла ошибка при сохранении данных.');
            }
        });
    });
});
