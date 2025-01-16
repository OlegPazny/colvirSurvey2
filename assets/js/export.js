const { jsPDF } = window.jspdf;

document.querySelectorAll('.export-to-pdf').forEach(button => {
    button.addEventListener('click', function () {
        const targetId = this.dataset.target; // Получаем ID целевого элемента
        const targetElement = document.querySelector(targetId);
        const accordionButton = targetElement.previousElementSibling.querySelector('.accordion-button');
        const pdfTitle = accordionButton ? accordionButton.textContent.trim() : "export";
        const formGroup = targetElement.querySelector('.form-group.card-body');

        let isFormGroup = false;
        let textInputs = []; // Инициализируем переменные
        let buttonInputs = [];
        let tempTexts = []; // Переменная для временных элементов

        if (formGroup !== null) {
            isFormGroup = true;
            textInputs = formGroup.querySelectorAll('input[type="text"]');
            buttonInputs = formGroup.querySelectorAll('input[type="button"]');

            // Создаем временные элементы
            tempTexts = Array.from(textInputs).map(input => {
                const label = input.previousElementSibling;
                const tempSpan = document.createElement('div');
                tempSpan.textContent = input.value;
                tempSpan.className = 'temp-text';

                if (label && label.tagName === 'LABEL') {
                    label.after(tempSpan);
                } else {
                    input.parentElement.appendChild(tempSpan);
                }

                input.style.display = 'none';
                return { input, tempSpan };
            });

            // Скрываем кнопки
            buttonInputs.forEach(button => {
                button.style.display = 'none';
            });

            formGroup.querySelectorAll('label').forEach(label => {
                label.style.fontWeight = 'bold';
            });
        }

        this.style.display = 'none';

        const dpi = 200; // Устанавливаем плотность точек
        const scale = dpi / 96; // Масштабируем относительно стандартного DPI
        domtoimage.toPng(targetElement, {
            width: targetElement.offsetWidth * scale,
            height: targetElement.offsetHeight * scale,
            style: {
                transform: 'scale(2)', // Масштабируем
                transformOrigin: 'top left', // Устанавливаем начальную точку масштабирования
                width: targetElement.offsetWidth + "px",
                height: targetElement.offsetHeight + "px"
            }
        })
            .then(dataUrl => {
                const pdf = new jsPDF("p", "mm", "a4");
                pdf.setFont("Roboto", "normal");
                pdf.setFont("Roboto", "bold");
                const pdfWidth = pdf.internal.pageSize.getWidth();
                const imgProps = pdf.getImageProperties(dataUrl);
                const imgWidth = pdfWidth;
                const imgHeight = (imgProps.height * pdfWidth) / imgProps.width;

                pdf.internal.pageSize.setHeight(imgHeight);

                pdf.addImage(dataUrl, "PNG", 0, 0, imgWidth, imgHeight);
                pdf.save(`${pdfTitle}.pdf`);

                if (isFormGroup) {
                    // Восстанавливаем элементы
                    tempTexts.forEach(({ input, tempSpan }) => {
                        tempSpan.remove();
                        input.style.display = '';
                    });

                    buttonInputs.forEach(button => {
                        button.style.display = '';
                    });

                    formGroup.querySelectorAll('label').forEach(label => {
                        label.style.fontWeight = '';
                    });
                }

                this.style.display = 'inline-block';
            })
            .catch(error => {
                console.error("Ошибка при создании PDF:", error);

                if (isFormGroup) {
                    // Восстанавливаем элементы в случае ошибки
                    tempTexts.forEach(({ input, tempSpan }) => {
                        tempSpan.remove();
                        input.style.display = '';
                    });

                    buttonInputs.forEach(button => {
                        button.style.display = '';
                    });

                    formGroup.querySelectorAll('label').forEach(label => {
                        label.style.fontWeight = '';
                    });
                }

                this.style.display = 'inline-block';
            });
    });
});
