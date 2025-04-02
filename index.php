<?php
session_start();

if($_SESSION['loggedin']!==true){
    header("Location: ./auth.php");
    die();
}

require_once "./assets/api/survey_functions.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="./assets/css/styles.css">
    <title>Опрос</title>
</head>

<body>
    <div class="container mt-5">
        <div class="survey-section end">
            <div class="survey-header">Спасибо за участие в опросе!</div>
        </div>
        <form id="surveyForm" method="POST">
            <div class="survey-section">
                <div class="survey-header">Улучшаем нашу работу вместе! 2025</div>
                <p>Коллеги, просим вас пройти опрос, где вы сможете поделиться своим взглядом на нашу корпоративную культуру и рабочие процессы.</p>
                <p>Ваше мнение очень важно для нас, т.к. мы стремимся создать пространство, где каждому будет комфортно и продуктивно работать.</p>
                <p>Ваши ответы помогут:</p>
                <ul>
                    <li>понять, что уже хорошо работает, а что можно улучшить;</li>
                    <li>создать условия, которые вдохновляют на сотрудничество и развитие.</li>
                </ul>
                <p>Опрос анонимный.<br>Его прохождение займёт не более 15 минут.</p>
            </div>
            <!-- Section 1 -->
            <div class="survey-section">
                <div class="survey-header">Вводная информация</div>
                <!-- Question 1: Select -->
                <div class="mb-3">
                    <label for="q1" class="form-label required">В каком подразделении Вы работаете?</label>
                    <select class="form-select" id="q1" name="q1" required>
                        <option value="" selected disabled>Выберите подразделение</option>
                        <?php
                        foreach ($departments as $department) {
                            echo ("<option value='" . $department[0] . "'>" . $department[1] . "</option>");
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="q2" class="form-label">Сколько лет вы работаете в компании?</label>
                    <select class="form-select" id="q2" name="q2">
                        <option selected disabled>Выберите количество лет</option>
                        <option value="less than 1">До 1 года</option>
                        <option value="between 1 and 2">От 1 года до 2 лет</option>
                        <option value="between 2 and 5">От 2 до 5 лет</option>
                        <option value="between 5 and 10">От 5 до 10 лет</option>
                        <option value="more than 10">Более 10 лет</option>
                        <option value="none">Не хочу отвечать на вопрос</option>
                    </select>
                </div>
            </div>

            <!-- Section 2 -->
            <div class="survey-section">
                <div class="survey-header">Блок №1</div>
                <div class="survey-description">Просим Вас ответить на вопросы опросника «Да» или «Нет».</div>
                <?php
                radioLine("Знаю ли я, что от меня ожидается на работе?", "3");
                radioLine("Располагаю ли я доступом к информации, а также необходимыми знаниями внутренних процедур для правильного выполнения моей работы?", "4");
                radioLine("Есть ли у меня на работе возможность ежедневно заниматься тем, что я умею делать лучше всего?", "5");
                radioLine("Получал ли я за последние 30 дней благодарность или одобрение за хорошо выполненную работу?", "6");
                radioLine("Есть ли у меня ощущение, что мой непосредственный руководитель или кто-то другой на работе заботится обо мне как о личности?", "7");
                radioLine("Есть ли у меня на работе человек, который поощряет мой рост (профессиональный и личностный)?", "8");
                radioLine("Есть ли у меня ощущение, что на работе считаются с моим мнением?", "9");
                radioLine("Ощущаю ли я взаимосвязь выполненных мною задач с общими целями компании?", "10");
                radioLine("Считают ли мои коллеги своим долгом выполнять работу качественно?", "11");
                ?>
                <div class="mb-3">
                    <label for="q1" class="form-label required">Оцените по шкале от 0 до 10, вероятность вашей
                        рекомендации Компании, как достойного места работы (где 0 – это никогда не порекомендую, а 10 –
                        регулярно рекомендую):</label>
                    <select class="form-select" id="q12" name="q12" required>
                        <?php
                        for ($i = 0; $i <= 10; $i++) {
                            $value = $i / 10;
                            echo ("<option value='" . $value . "'>" . $i . "</option>");
                        }
                        ?>
                    </select>
                </div>
                <?php
                radioLine("За последние шесть месяцев кто-нибудь на работе беседовал со мной (о моем прогрессе либо о факторах, которые мне мешают в работе), определял зоны моего развития?", "13");
                radioLine("Были ли у меня на работе в течение прошедшего года возможности для учебы и роста", "14");
                ?>
            </div>
            <div class="survey-section">
                <div class="survey-header">Блок №2</div>
                <div>
                    <label class="form-label required">Поставьте наиболее подходящую оценку следующим утверждениям<br>
                        (где 4 - да; 3 - скорее да, чем нет; 2 - нет; 1 - мне все равно)</label>
                    <div class="table-responsive">
                        <table class="table table-bordered matrix-table">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>1</th>
                                    <th>2</th>
                                    <th>3</th>
                                    <th>4</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                matrixLine("Я понимаю свои задачи и функции", "15");
                                matrixLine("Я знаю, что от меня ждет руководство", "16");
                                matrixLine("Я знаю, по каким критериям оценивается моя работа", "17");
                                matrixLine("В компании созданы все условия, чтобы я качественно выполнял свою работу", "18");
                                matrixLine("Если я работаю хорошо и старательно, руководитель положительно отзывается обо мне (непосредственный руководитель, например TeamLeader для программиста, Специалиста по тестированию и тд)", "19");
                                matrixLine("Руководитель ценит мои заслуги, отмечает успехи (непосредственный руководитель, например TeamLeader для программиста, Специалиста по тестированию и тд)", "20");
                                matrixLine("Мой непосредственный руководитель и коллеги заинтересованы в том, чтобы я работал лучше", "21");
                                matrixLine("Ко мне часто обращаются за советом коллеги и/или руководитель", "22");
                                matrixLine("Я обучаюсь в процессе работы, узнаю много нового, мне помогают справиться с интересными задачами", "23");
                                matrixLine("Я понимаю, что моя работа важна для других и доволен, что тружусь в компании", "24");
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="survey-section">
                <div class="survey-header">Ваши комментарии и предложения</div>
                <textarea class="form-control" id="comment" rows="3" name="comment"></textarea>
            </div>
            <!-- Submit Section -->
            <div class="survey-section">
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Отправить</button>
                </div>
            </div>

        </form>
    </div>
</body>
<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
    integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
    crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>
<script src="./assets/js/script.js"></script>

</html>