const { jsPDF } = window.jspdf;

document.querySelectorAll('.export-to-pdf').forEach(button => {
    button.addEventListener('click', function () {
        const targetId = this.dataset.target; // Получаем ID целевого элемента
        const targetElement = document.querySelector(targetId);
        // Извлекаем текст из кнопки аккордеона
        const accordionButton = targetElement.previousElementSibling.querySelector('.accordion-button');
        const pdfTitle = accordionButton ? accordionButton.textContent.trim() : "export";   
        this.style.display = 'none';
        domtoimage.toPng(targetElement)
            .then(dataUrl => {
                const pdf = new jsPDF("p", "mm", "a4");
                const pdfWidth = pdf.internal.pageSize.getWidth(); // Стандартная ширина PDF
                const imgProps = pdf.getImageProperties(dataUrl);
                const imgWidth = pdfWidth; // Подгоняем ширину под PDF
                const imgHeight = (imgProps.height * pdfWidth) / imgProps.width; // Соотношение сторон
                
                // Увеличиваем высоту страницы PDF под содержимое
                pdf.internal.pageSize.setHeight(imgHeight);

                // Добавляем изображение
                pdf.addImage(dataUrl, "PNG", 0, 0, imgWidth, imgHeight);
                pdf.save(`${pdfTitle}.pdf`);
                this.style.display = 'inline-block';
            })
            .catch(error => {
                console.error("Ошибка при создании PDF:", error);
                // Восстанавливаем кнопку, если произошла ошибка
                this.style.display = 'inline-block';
            });
    });
});