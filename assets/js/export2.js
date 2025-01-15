document.querySelectorAll('.export-to-pdf').forEach(button => {
    button.addEventListener('click', function () {
        const targetId = this.dataset.target; // Получаем ID целевого элемента
        const targetElement = document.querySelector(targetId);
        const pdfTitle = "export"; // Название файла PDF

        // Настраиваем опции
        const opt = {
            printable: targetElement,
            type: 'html',
            targetStyles: ['*'], // Применяем все стили к экспортируемому контенту
            ignoreElements: ['.export-to-pdf'], // Игнорируем кнопку экспорта при печати
            documentTitle: pdfTitle,
            header: pdfTitle,
            style: `
                /* Дополнительные стили для корректного отображения */
                body {
                    font-family: Arial, sans-serif;
                    font-size: 14px;
                }
                .accordion-item {
                    margin-bottom: 20px;
                    border: 1px solid #ccc;
                    padding: 10px;
                    background-color: #f9f9f9;
                }
                .square {
                    border: 1px solid #ddd;
                    padding: 10px;
                    margin-bottom: 10px;
                }
                .square h3 {
                    margin-top: 0;
                    font-size: 18px;
                    color: #333;
                    text-decoration: underline;
                }
                .square h1 {
                    margin-top: 5px;
                    font-size: 24px;
                    color: #555;
                }
                .graphall1, .graphall2, .graphPercentages {
                    margin-top: 20px;
                }
                /* Добавьте другие необходимые стили */
            `
        };

        // Преобразуем HTML в PDF
        printJS(opt);
    });
});
