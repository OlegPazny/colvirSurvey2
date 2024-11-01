<?php
require_once "assets/api/db_connect.php";

$data = mysqli_query($db, "SELECT * FROM `results`");
$data = mysqli_fetch_all($data);

$data_prev = mysqli_query($db, "SELECT * FROM `prev_results`");
$data_prev = mysqli_fetch_all($data_prev);

$questions = [
    3 => "Знаю ли я, что от меня ожидается на работе?",
    4 => "Располагаю ли я доступом к информации, а также необходимыми знаниями внутренних процедур для правильного выполнения моей работы?",
    5 => "Есть ли у меня на работе возможность ежедневно заниматься тем, что я умею делать лучше всего?",
    6 => "Получал ли я за последние 30 дней благодарность или одобрение за хорошо выполненную работу?",
    7 => "Есть ли у меня ощущение, что мой непосредственный руководитель или кто-то другой на работе заботится обо мне как о личности?",
    8 => "Есть ли у меня на работе человек, который поощряет мой рост (профессиональный и личностный)?",
    9 => "Есть ли у меня ощущение, что на работе считаются с моим мнением?",
    10 => "Ощущаю ли я взаимосвязь выполненных мною задач с общими целями компании?",
    11 => "Считают ли мои коллеги своим долгом выполнять работу качественно?",
    12 => "Оцените по шкале от 0 до 10, вероятность вашей рекомендации Компании, как достойного места работы (где 0 – это никогда не порекомендую, а 10 – регулярно рекомендую).",
    13 => "За последние шесть месяцев кто-нибудь на работе беседовал со мной (о моем прогрессе либо о факторах, которые мне мешают в работе), определял зоны моего развития?",
    14 => "Были ли у меня на работе в течение прошедшего года возможности для учебы и роста",
    15 => "Я понимаю свои задачи и функции",
    16 => "Я знаю, что от меня ждет руководство",
    17 => "Я знаю, по каким критериям оценивается моя работа",
    18 => "В компании созданы все условия, чтобы я качественно выполнял свою работу",
    19 => "Если я работаю хорошо и старательно, руководитель положительно отзывается обо мне",
    20 => "Руководитель ценит мои заслуги, отмечает успехи",
    21 => "Руководитель и коллеги заинтересованы в том, чтобы я работал лучше",
    22 => "Ко мне обращаются за советом коллеги и/или руководитель",
    23 => "Я обучаюсь в процессе работы, узнаю много нового, мне помогают справиться с интересными задачами",
    24 => "Я понимаю, что моя работа важна для других и доволен, что тружусь в компании"
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Метрики</title>
</head>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script> <!-- Подключение плагина -->


<style>
    td,
    tr,
    th {
        border-bottom: 1px solid gray;
        border-right: 1px solid gray;
    }

    table {
        display: none;
        font-size: 10px;
    }

    .graph1 {
        width: 400px
    }

    .container {
        display: flex;
        flex-direction: row;
        justify-content: space-around;
    }
</style>

<body>
    <div class="container">
        <div class="first">
            <h3>Текущий период</h3>
            <table>
                <thead>
                    <tr>
                        <th>id</th>
                        <th>Подразделение</th>
                        <th>Сколько лет</th>
                        <th>ОВ</th>
                        <th>ИВ</th>
                        <th>ЭВ</th>
                        <th>ЭВ</th>
                        <th>ЭВ</th>
                        <th>ИВ</th>
                        <th>ЭВ</th>
                        <th>ОВ</th>
                        <th>ОВ</th>
                        <th>ЭВ</th>
                        <th>ИВ</th>
                        <th>ИВ</th>
                        <th>ОВ</th>
                        <th>ОВ</th>
                        <th>ОВ</th>
                        <th>ИВ</th>
                        <th>ЭВ</th>
                        <th>ЭВ</th>
                        <th>ОВ</th>
                        <th>ИВ</th>
                        <th>ИВ</th>
                        <th>ОВ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sum_arr = []; //общее
                    $ov_arr = []; //организационное ОВ
                    $iv_arr = []; //интеллектуальное ИВ
                    $ev_arr = []; //эмоциональное ЭВ
                    $count = 0;
                    foreach ($data as $item) {

                        echo "<tr>";
                        for ($i = 0; $i <= 24; $i++) {
                            echo ("<td>" . $item[$i] . "</td>");
                        }
                        for ($i = 3; $i <= 24; $i++) {
                            $sum_arr[$i] += $item[$i];
                            if ($i == 3 || $i == 10 || $i == 11 || $i == 15 || $i == 16 || $i == 17 || $i == 21 || $i == 24) {
                                $ov_arr[$i] += $item[$i];
                            }
                            if ($i == 4 || $i == 8 || $i == 13 || $i == 14 || $i == 18 || $i == 22 || $i == 23) {
                                $iv_arr[$i] += $item[$i];
                            }
                            if ($i == 5 || $i == 6 || $i == 7 || $i == 9 || $i == 12 || $i == 19 || $i == 20) {
                                $ev_arr[$i] += $item[$i];
                            }
                        }
                        echo "</tr>";
                        $count++;
                    }
                    ?>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <?php
                        foreach ($sum_arr as $item) {
                            echo ("<td>" . round($item / $count, 5) . "</td>");
                        }
                        ?>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <?php
                        foreach ($sum_arr as $item) {
                            echo ("<td>" . round($item / $count * 100, 2) . "</td>");
                        }
                        ?>
                    </tr>
                </tbody>
            </table>
            <?php
            $people_amount = $count; //КР количество респондентов
            echo ("КР " . $people_amount . "<br>");


            //вовлеченность общая ВО=СБ*100/(МБ*КР)
            $questions_count_total = count($sum_arr); //МБ максимальный балл (в строке)
            echo ("МБ " . $questions_count_total . "<br>");
            $total_score = array_sum($sum_arr); //СБ сумма баллов
            echo ("СБ " . $total_score . "<br>");
            $max_score = $questions_count_total * $count;
            $involved = round(100 * $total_score / $max_score, 3);
            $involvement_total = round($total_score * 100 / $questions_count_total / $people_amount, 2);
            echo ("ВО " . $involvement_total . "<br>");
            echo ("___________<br>");

            //вовлеченность организационная ОВ=СБ*100/(МБ/КР)
            $total_score_ov = array_sum($ov_arr); //СБ сумма баллов
            echo ("СБ " . $total_score_ov . "<br>");
            $questions_count_ov = count($ov_arr); //МБ максимальный балл (в строке)
            echo ("МБ " . $questions_count_ov . "<br>");
            $involvement_org = round($total_score_ov * 100 / $questions_count_ov / $people_amount, 2);
            echo ("ОВ " . $involvement_org . "<br>");
            echo ("___________<br>");

            //вовлеченность интеллектуальная ИВ=СБ*100/(МБ*КР)
            $total_score_iv = array_sum($iv_arr); //СБ сумма баллов
            echo ("СБ " . $total_score_iv . "<br>");
            $questions_count_iv = count($iv_arr); //МБ максимальный балл (в строке)
            echo ("МБ " . $questions_count_iv . "<br>");
            $involvement_int = round($total_score_iv * 100 / $questions_count_iv / $people_amount, 2);
            echo ("ИВ " . $involvement_int . "<br>");
            echo ("___________<br>");


            //вовлеченность эмоциональная ЭВ=СБ*100/(МБ*КР)
            $total_score_ev = array_sum($ev_arr); //СБ сумма баллов
            echo ("СБ " . $total_score_iv . "<br>");
            $questions_count_ev = count($ev_arr); //МБ максимальный балл (в строке)
            echo ("МБ " . $questions_count_ev . "<br>");
            $involvement_emo = round($total_score_ev * 100 / $questions_count_ev / $people_amount, 2);
            echo ("ЭВ " . $involvement_emo . "<br>");
            echo ("___________<br>");

            $curr_avg_arr = [];
            foreach ($sum_arr as $key => $item) {
                $questionText = $questions[$key] ?? "Неизвестный вопрос"; // заменяем индекс на текст вопроса
                $curr_avg_arr[$questionText] = round($item / $count * 100, 2);
            }

            ?>
        </div>
        <div class="second">
            <h3>Предыдущий период</h3>
            <table>
                <thead>
                    <tr>
                        <th>id</th>
                        <th>Подразделение</th>
                        <th>Сколько лет</th>
                        <th>ОВ</th>
                        <th>ИВ</th>
                        <th>ЭВ</th>
                        <th>ЭВ</th>
                        <th>ЭВ</th>
                        <th>ИВ</th>
                        <th>ЭВ</th>
                        <th>ОВ</th>
                        <th>ОВ</th>
                        <th>ЭВ</th>
                        <th>ИВ</th>
                        <th>ИВ</th>
                        <th>ОВ</th>
                        <th>ОВ</th>
                        <th>ОВ</th>
                        <th>ИВ</th>
                        <th>ЭВ</th>
                        <th>ЭВ</th>
                        <th>ОВ</th>
                        <th>ИВ</th>
                        <th>ИВ</th>
                        <th>ОВ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sum_arr = []; //общее
                    $ov_arr = []; //организационное ОВ
                    $iv_arr = []; //интеллектуальное ИВ
                    $ev_arr = []; //эмоциональное ЭВ
                    $count = 0;
                    foreach ($data_prev as $item) {

                        echo "<tr>";
                        for ($i = 0; $i <= 24; $i++) {
                            echo ("<td>" . $item[$i] . "</td>");
                        }
                        for ($i = 3; $i <= 24; $i++) {
                            $sum_arr[$i] += $item[$i];
                            if ($i == 3 || $i == 10 || $i == 11 || $i == 15 || $i == 16 || $i == 17 || $i == 21 || $i == 24) {
                                $ov_arr[$i] += $item[$i];
                            }
                            if ($i == 4 || $i == 8 || $i == 13 || $i == 14 || $i == 18 || $i == 22 || $i == 23) {
                                $iv_arr[$i] += $item[$i];
                            }
                            if ($i == 5 || $i == 6 || $i == 7 || $i == 9 || $i == 12 || $i == 19 || $i == 20) {
                                $ev_arr[$i] += $item[$i];
                            }
                        }
                        echo "</tr>";
                        $count++;
                    }
                    ?>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <?php
                        foreach ($sum_arr as $item) {
                            echo ("<td>" . round($item / $count, 5) . "</td>");
                        }
                        ?>
                    </tr>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <?php
                        foreach ($sum_arr as $item) {
                            echo ("<td>" . round($item / $count * 100, 2) . "</td>");
                        }
                        ?>
                    </tr>
                </tbody>
            </table>
            <?php
            $people_amount = $count; //КР количество респондентов
            echo ("КР " . $people_amount . "<br>");


            //вовлеченность общая ВО=СБ*100/(МБ*КР)
            $questions_count_total = count($sum_arr); //МБ максимальный балл (в строке)
            echo ("МБ " . $questions_count_total . "<br>");
            $total_score = array_sum($sum_arr); //СБ сумма баллов
            echo ("СБ " . $total_score . "<br>");
            $max_score = $questions_count_total * $count;
            $involved = round(100 * $total_score / $max_score, 3);
            $involvement_total = round($total_score * 100 / $questions_count_total / $people_amount, 2);
            echo ("ВО " . $involvement_total . "<br>");
            echo ("___________<br>");

            //вовлеченность организационная ОВ=СБ*100/(МБ/КР)
            $total_score_ov = array_sum($ov_arr); //СБ сумма баллов
            echo ("СБ " . $total_score_ov . "<br>");
            $questions_count_ov = count($ov_arr); //МБ максимальный балл (в строке)
            echo ("МБ " . $questions_count_ov . "<br>");
            $involvement_org_prev = round($total_score_ov * 100 / $questions_count_ov / $people_amount, 2);
            echo ("ОВ " . $involvement_org_prev . "<br>");
            echo ("___________<br>");

            //вовлеченность интеллектуальная ИВ=СБ*100/(МБ*КР)
            $total_score_iv = array_sum($iv_arr); //СБ сумма баллов
            echo ("СБ " . $total_score_iv . "<br>");
            $questions_count_iv = count($iv_arr); //МБ максимальный балл (в строке)
            echo ("МБ " . $questions_count_iv . "<br>");
            $involvement_int_prev = round($total_score_iv * 100 / $questions_count_iv / $people_amount, 2);
            echo ("ИВ " . $involvement_int_prev . "<br>");
            echo ("___________<br>");


            //вовлеченность эмоциональная ЭВ=СБ*100/(МБ*КР)
            $total_score_ev = array_sum($ev_arr); //СБ сумма баллов
            echo ("СБ " . $total_score_iv . "<br>");
            $questions_count_ev = count($ev_arr); //МБ максимальный балл (в строке)
            echo ("МБ " . $questions_count_ev . "<br>");
            $involvement_emo_prev = round($total_score_ev * 100 / $questions_count_ev / $people_amount, 2);
            echo ("ЭВ " . $involvement_emo_prev . "<br>");
            echo ("___________<br>");

            $prev_avg_arr = [];
            foreach ($sum_arr as $key => $item) {
                $questionText = $questions[$key] ?? "Неизвестный вопрос"; // заменяем индекс на текст вопроса
                $prev_avg_arr[$questionText] = round($item / $count * 100, 2);
            }

            $chartData = [
                "current" => [
                    "ОВ" => $involvement_org,
                    "ИВ" => $involvement_int,
                    "ЭВ" => $involvement_emo
                ],
                "previous" => [
                    "ОВ" => $involvement_org_prev,
                    "ИВ" => $involvement_int_prev,
                    "ЭВ" => $involvement_emo_prev
                ]
            ];
            ?>
        </div>
    </div>
    <div class="graph1">
        <canvas id="myChart" width="200" height="100"></canvas>
    </div>
    <div class="graph2">
        <canvas id="myChart2" width="200" height="100"></canvas>
    </div>
</body>
<script>
    // Передаем данные из PHP в JavaScript через JSON
    const chartData = <?php echo json_encode($chartData); ?>;
    const labels = <?= json_encode(array_keys($curr_avg_arr)) ?>;
    const currentData = <?= json_encode(array_values($curr_avg_arr)) ?>;
    const previousData = <?= json_encode(array_values($prev_avg_arr)) ?>;
    // Предполагаем, что currentData и previousData имеют одинаковую длину
    let sortedData = labels.map((label, index) => ({
        label: label,
        currentValue: currentData[index],
        previousValue: previousData[index],
    }));

    // Сортируем по текущим значениям в порядке убывания
    sortedData.sort((a, b) => b.currentValue - a.currentValue);

    // Создаем новые массивы для меток и данных
    const sortedLabels = sortedData.map(item => item.label);
    const sortedCurrentData = sortedData.map(item => item.currentValue);
    const sortedPreviousData = sortedData.map(item => item.previousValue);

    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('myChart').getContext('2d');
        const myChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ["ОВ", "ИВ", "ЭВ"], // Метки для каждого критерия
                datasets: [{
                        label: 'Предыдущий период',
                        data: Object.values(chartData.previous),
                        backgroundColor: '#2E5B9B', // Цвет для предыдущего периода
                        borderColor: '#2E5B9B',
                        borderWidth: 1
                    },
                    {
                        label: 'Текущий период',
                        data: Object.values(chartData.current),
                        backgroundColor: '#999B9A', // Основной цвет столбцов для текущего периода
                        borderColor: '#999B9A',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: true
                    },
                    datalabels: { // Настройки плагина DataLabels
                        anchor: 'end',
                        align: 'top',
                        formatter: function(value) {
                            return value.toFixed(2); // Формат значений
                        },
                        color: '#333', // Цвет текста
                        font: {
                            weight: 'bold'
                        }
                    }
                },
                barPercentage: 0.4, // Уменьшенная ширина столбцов
                categoryPercentage: 0.8 // Плотное расположение столбцов
            },
            plugins: [ChartDataLabels] // Включаем плагин
        });
    });
    const ctx2 = document.getElementById('myChart2').getContext('2d');
    const myChart = new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: sortedLabels,
            datasets: [{
                    label: 'Текущий период',
                    data: sortedCurrentData,
                    backgroundColor: '#2E5B9B', // Корпоративный синий цвет
                    borderColor: '#2E5B9B',
                    borderWidth: 1,
                    barThickness: 10, // Уменьшаем ширину столбцов
                },
                {
                    label: 'Предыдущий период',
                    data: sortedPreviousData,
                    backgroundColor: '#999B9A', // Серый цвет
                    borderColor: '#999B9A',
                    borderWidth: 1,
                    barThickness: 10, // Уменьшаем ширину столбцов
                },
            ],
        },
        options: {
            indexAxis: 'y', // Делаем столбцы горизонтальными
            responsive: true,
            scales: {
                x: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Показатели (%)',
                    },
                },
                y: {
                    title: {
                        display: true,
                        text: 'Вопросы',
                    },
                },
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    enabled: false, // Отключаем подсказки
                },
                // Добавляем значения столбцов справа
                datalabels: {
                    anchor: 'end',
                    align: 'end',
                    formatter: (value) => `${value}%`, // Форматируем отображаемое значение
                    color: '#000', // Цвет текста
                },
            },
        },
        plugins: [ChartDataLabels] // Убедитесь, что у вас подключен плагин datalabels
    });
</script>

</html>