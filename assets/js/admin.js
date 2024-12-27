$(document).ready(function() {
    $('#surveyForm').on('submit', function (event) {
        event.preventDefault();
        // Собираем данные формы
        let formData = $(this).serialize();
        // Отправляем AJAX-запрос
        $.ajax({
            url: 'assets/api/update_survey.php',
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    alert('Данные о текущем исследовании обновлены успешно.');
                } else {
                    alert('Ошибка при обновлении: ' + response.error);
                }
            },
            error: function () {
                alert('Не удалось выполнить запрос.');
            }
        });
    });

    // Начало нового опроса
    $("#startNewSurvey").click(function() {
        if (confirm("Вы уверены, что хотите начать новый опрос? Это действие нельзя отменить.")) {
            $.ajax({
                url: 'assets/api/new_survey.php', // Сдвиг таблиц и бэкап БД
                type: 'POST',
                success: function(response) {
                    alert(response);
                },
                error: function() {
                    alert("Ошибка при запуске нового опроса.");
                }
            });
        }
    });
});
