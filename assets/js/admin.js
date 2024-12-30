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

    $("#startNewSurvey").click(function() {
        if (confirm("Вы уверены, что хотите начать новый опрос? Это действие нельзя отменить.")) {
            // Шаг 1: Скачиваем бэкап
            window.location.href = 'assets/api/new_survey.php?action=backup&timestamp=' + new Date().getTime();
    
            // Шаг 2: Сдвигаем таблицы после завершения бэкапа
            setTimeout(function() {
                $.ajax({
                    url: 'assets/api/new_survey.php',
                    type: 'POST',
                    success: function(response) {
                        let result = JSON.parse(response);
                        alert(result.message);
                    },
                    error: function() {
                        alert("Ошибка при запуске нового опроса.");
                    }
                });
            }, 2000); // Устанавливаем небольшую задержку для завершения скачивания
        }
    });
    
});
