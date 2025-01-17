$(document).ready(function() {
    $(".save-btn").click(function() {
        // Найти ближайший родительский блок (карточку)
        const parentCard = $(this).closest(".card-body");

        // Найти поля ввода внутри этого блока
        const conclusion = parentCard.find('input[name="conclusion"]').val();
        const recommendations = parentCard.find('input[name="recommendations"]').val();

        // Получить идентификатор департамента из атрибута кнопки
        const departmentIds = $(this).data("department-ids");

        // Отправить данные на сервер через AJAX
        $.ajax({
            url: './assets/api/save_department_rec.php',
            type: 'POST',
            data: {
                department_ids: departmentIds,
                conclusion: conclusion,
                recommendations: recommendations
            },
            success: function(response) {
                parentCard.append('<div class="alert alert-success mt-3">Данные успешно сохранены</div>');
                setTimeout(() => {
                    parentCard.find('.alert').remove();
                }, 3000);
            },
            error: function() {
                parentCard.append('<div class="alert alert-danger mt-3">Ошибка сохранения данных</div>');
                setTimeout(() => {
                    parentCard.find('.alert').remove();
                }, 3000);
            }
        });
    });
});
