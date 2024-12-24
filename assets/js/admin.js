$(document).ready(function() {
    // Сохранение данных исследования
    $("#surveyForm").submit(function(event) {
        event.preventDefault();
        
        if (confirm("Вы уверены, что хотите сохранить название исследования и загрузить данные сотрудников?")) {
            const formData = new FormData(this);

            $.ajax({
                url: 'assets/api/update_survey.php', // Сохранение названия и загрузка Excel
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    alert("Данные успешно сохранены!");
                },
                error: function() {
                    alert("Ошибка при сохранении данных.");
                }
            });
        }
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
