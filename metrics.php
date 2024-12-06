<?php
require_once "assets/api/db_connect.php";

$data = mysqli_query($db, "SELECT * FROM `results`");
$data = mysqli_fetch_all($data);

$data_prev = mysqli_query($db, "SELECT * FROM `prev_results`");
$data_prev = mysqli_fetch_all($data_prev);

$data_prev_prev = mysqli_query($db, "SELECT * FROM `prev_prev_results`");
$data_prev_prev = mysqli_fetch_all($data_prev_prev);

$employees = mysqli_query($db, "SELECT * FROM `employees`");
$employees = mysqli_fetch_all($employees);

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
function roundToNearestFive($number)
{
    return round($number / 5) * 5;
}
function generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees)
{
    echo '<h1>Дашборд ' . $departmentNameRu . '</h1>
    <div class="container"><div class="first"><h3>Текущий период</h3>';
    $sum_arr = []; //общее
    $ov_arr = []; //организационное ОВ
    $iv_arr = []; //интеллектуальное ИВ
    $ev_arr = []; //эмоциональное ЭВ
    $currentPositiveRates = []; //положительные ответы за последний период
    $promouters = 0; //промоутеры
    $critics = 0; //критики
    $count = 0;
    $total_promouters = 0; //промоутеры
    $total_critics = 0; //критики
    $total_sum_arr = []; // Для подсчёта по всей компании
    $total_count = 0; // Количество всех участников по компании
    // Расчеты для текущего периода
    foreach ($questions as $questionId => $questionText) {
        $currentPositive = 0;
        $currentTotal = 0;

        foreach ($data as $item) {
            $isDepartment = empty($departmentIds) || in_array($item[1], $departmentIds);
            if ($isDepartment && isset($item[$questionId])) {
                $currentPositive += $item[$questionId] == 1 ? 1 : 0;
                $currentTotal++;
            }
        }

        $currentPositiveRates[$questionId] = $currentTotal > 0 ? ($currentPositive / $currentTotal) * 100 : 0;
    }
    foreach ($data as $item) {
        $isDepartment = count($departmentIds) > 0 && in_array($item[1], $departmentIds);
        // Всегда собираем данные для всей компании
        for ($i = 3; $i <= 24; $i++) {
            $total_sum_arr[$i] += $item[$i];
        }
        if ($item[12] >= 0.9) {
            $total_promouters++;
        } elseif ($item[12] <= 0.6) {
            $total_critics++;
        }
        $total_count++;
        // Считаем только для департамента, если он указан
        if ($isDepartment || empty($departmentIds)) {
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
                    if ($i == 12) {
                        if ($item[$i] >= 0.9 && $item[$i] <= 1) {
                            $promouters++;
                        } elseif ($item[$i] >= 0 && $item[$i] <= 0.6) {
                            $critics++;
                        }
                    }
                }
            }
            $count++;
        }
    }
    // Подсчёт общего количества сотрудников и участников
    //для департамента
    $totalEmployees = 0;
    $participants = $count; // Количество респондентов уже подсчитано выше
    //для компании
    $totalEmployeesCompany = 0;
    $participantsCompany = $total_count; // Количество респондентов уже подсчитано выше
    foreach ($employees as $employee) {
        $totalEmployeesCompany++;
    }
    foreach ($employees as $employee) {
        if (in_array($employee[1], $departmentIds)) {
            $totalEmployees++;
        }
    }
    $participationRateCompany = $totalEmployeesCompany > 0 ? round(($participantsCompany / $totalEmployeesCompany) * 100, 2) : 0;
    $participationRate = $totalEmployees > 0 ? round(($participants / $totalEmployees) * 100, 2) : 0;
    $people_amount = $count; //КР количество респондентов
    echo ("КР " . $people_amount . "<br>");
    //вовлеченность по всей компании
    $questions_count_total_company = count($total_sum_arr);
    $total_score_company = array_sum($total_sum_arr);
    $involvement_total_company = $total_count > 0 ? round($total_score_company * 100 / $questions_count_total_company / $total_count, 2) : 0;
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
    echo ("<br>");
    ${$departmentNameEn . "_curr_avg_arr"} = [];
    foreach ($sum_arr as $key => $item) {
        $questionText = $questions[$key] ?? "Неизвестный вопрос"; // заменяем индекс на текст вопроса
        ${$departmentNameEn . "_curr_avg_arr"}[$questionText] = round($item / $count * 100, 2);
    }
    //eNPS
    $promouter_percent = round($promouters / $people_amount * 100, 2);
    $critics_percent = round($critics / $people_amount * 100, 2);
    $eNPS = $promouter_percent - $critics_percent;
    $eNPSCompany = $total_count > 0 ? round((($total_promouters - $total_critics) / $total_count) * 100, 2) : 0;
    echo '</div><div class="second"><h3>Предыдущий период</h3>';
    $sum_arr = []; //общее
    $ov_arr = []; //организационное ОВ
    $iv_arr = []; //интеллектуальное ИВ
    $ev_arr = []; //эмоциональное ЭВ
    $previousPositiveRates = []; //положительные ответы за предыдущий период
    $differences = [];
    $count = 0;
    $total_sum_arr_prev = []; // Для подсчёта по всей компании
    $total_count_prev = 0; // Количество всех участников по компании
    // Расчеты для предыдущего периода
    foreach ($questions as $questionId => $questionText) {
        $previousPositive = 0;
        $previousTotal = 0;

        foreach ($data_prev as $item) {
            $isDepartment = empty($departmentIds) || in_array($item[1], $departmentIds);
            if ($isDepartment && isset($item[$questionId])) {
                $previousPositive += $item[$questionId] == 1 ? 1 : 0;
                $previousTotal++;
            }
        }

        $previousPositiveRates[$questionId] = $previousTotal > 0 ? ($previousPositive / $previousTotal) * 100 : 0;
    }
    foreach ($data_prev as $item) {
        $isDepartment = count($departmentIds) > 0 && in_array($item[1], $departmentIds);
        // Всегда собираем данные для всей компании
        for ($i = 3; $i <= 24; $i++) {
            $total_sum_arr_prev[$i] += $item[$i];
        }
        if ($item[12] >= 0.9) {
            $total_promouters++;
        } elseif ($item[12] <= 0.6) {
            $total_critics++;
        }
        $total_count_prev++;
        // Считаем только для департамента, если он указан
        if ($isDepartment || empty($departmentIds)) {
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
                    if ($i == 12) {
                        if ($item[$i] >= 0.9 && $item[$i] <= 1) {
                            $promouters++;
                        } elseif ($item[$i] >= 0 && $item[$i] <= 0.6) {
                            $critics++;
                        }
                    }
                }
            }
            $count++;
        }
    }
    $people_amount = $count; //КР количество респондентов
    echo ("КР " . $people_amount . "<br>");
    $questions_count_total_company_prev = count($total_sum_arr_prev);
    $total_score_company_prev = array_sum($total_sum_arr_prev);
    $involvement_total_company_prev = $total_count_prev > 0 ? round($total_score_company_prev * 100 / $questions_count_total_company_prev / $total_count_prev, 2) : 0;
    //вовлеченность общая ВО=СБ*100/(МБ*КР)
    $questions_count_total = count($sum_arr); //МБ максимальный балл (в строке)
    echo ("МБ " . $questions_count_total . "<br>");
    $total_score = array_sum($sum_arr); //СБ сумма баллов
    echo ("СБ " . $total_score . "<br>");
    $max_score = $questions_count_total * $count;
    $involved = round(100 * $total_score / $max_score, 3);
    $involvement_total_prev = round($total_score * 100 / $questions_count_total / $people_amount, 2);
    echo ("ВО " . $involvement_total_prev . "<br>");
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
    echo ("<br>");
    ${$departmentNameEn . "_prev_avg_arr"} = [];
    foreach ($sum_arr as $key => $item) {
        $questionText = $questions[$key] ?? "Неизвестный вопрос"; // заменяем индекс на текст вопроса
        ${$departmentNameEn . "_prev_avg_arr"}[$questionText] = round($item / $count * 100, 2);
    }
    $eNPS_prev = $promouter_percent - $critics_percent;
    echo '</div><div class="third"><h3>Предпредыдущий период</h3>';
    $sum_arr = []; //общее
    $ov_arr = []; //организационное ОВ
    $iv_arr = []; //интеллектуальное ИВ
    $ev_arr = []; //эмоциональное ЭВ
    $count = 0;
    foreach ($data_prev_prev as $item) {
        if (count($departmentIds) > 0) {
            // Проверяем, принадлежит ли $item[1] массиву $departmentIds
            if (in_array($item[1], $departmentIds)) {
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
                $count++;
            }
        } else {
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
            $count++;
        }
    }
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
    $involvement_org_prev_prev = round($total_score_ov * 100 / $questions_count_ov / $people_amount, 2);
    echo ("ОВ " . $involvement_org_prev_prev . "<br>");
    echo ("___________<br>");
    //вовлеченность интеллектуальная ИВ=СБ*100/(МБ*КР)
    $total_score_iv = array_sum($iv_arr); //СБ сумма баллов
    echo ("СБ " . $total_score_iv . "<br>");
    $questions_count_iv = count($iv_arr); //МБ максимальный балл (в строке)
    echo ("МБ " . $questions_count_iv . "<br>");
    $involvement_int_prev_prev = round($total_score_iv * 100 / $questions_count_iv / $people_amount, 2);
    echo ("ИВ " . $involvement_int_prev_prev . "<br>");
    echo ("___________<br>");
    //вовлеченность эмоциональная ЭВ=СБ*100/(МБ*КР)
    $total_score_ev = array_sum($ev_arr); //СБ сумма баллов
    echo ("СБ " . $total_score_iv . "<br>");
    $questions_count_ev = count($ev_arr); //МБ максимальный балл (в строке)
    echo ("МБ " . $questions_count_ev . "<br>");
    $involvement_emo_prev_prev = round($total_score_ev * 100 / $questions_count_ev / $people_amount, 2);
    echo ("ЭВ " . $involvement_emo_prev_prev . "<br>");
    echo ("<br>");
    // Вычисление разницы положительных ответов
    foreach ($questions as $questionId => $questionText) {
        $currentRate = $currentPositiveRates[$questionId] ?? 0;
        $previousRate = $previousPositiveRates[$questionId] ?? 0;
        $differences[$questionId] = $currentRate - $previousRate;
    }
    $differences = array_values($differences);
    // Округляем разницу до ближайшего числа, кратного 5
    $differences = array_map('roundToNearestFive', $differences);
    // Фильтруем разницу, исключая 0 и соответствующие метки
    $filteredDifferences = [];
    $filteredLabels = [];
    $labels = array_values($questions);
    foreach ($labels as $index => $label) {
        if ((int)$differences[$index] !== 0) {  // Оставляем только вопросы с ненулевой разницей
            $filteredDifferences[] = $differences[$index];
            $filteredLabels[] = $label;
        }
    }
    
    // Переиндексация массива после фильтрации
    $differences = array_values($differences);
    
    echo "</div></div><div class='square-block'><div class='square'>
        <u><h3>Вовлеченность по компании (тек./прош.)</h3></u>
        <h1>" . $involvement_total_company . "%/" . $involvement_total_company_prev . "%</h1>
    </div>";
    echo "<div class='square'>
        <u><h3>Вовлеченность " . $departmentNameRu . "  (тек./прош.)</h3></u>
        <h1>" . $involvement_total . "%/" . $involvement_total_prev . "%</h1>
    </div>";
    echo "<div class='square'>
        <u><h3>Участники по компании (кол-во/процент)</h3></u>
        <h1>" . $totalEmployeesCompany . "/" . $participationRateCompany . "%</h1>
    </div>";
    if (count($departmentIds) > 0) {
        echo "<div class='square'>
            <u><h3>Участники по департаменту (кол-во/процент)</h3></u>
            <h1>" . $totalEmployees . "/" . $participationRate . "%</h1>
        </div>";
    }
    echo "<div class='square'>
        <u><h3>eNPS общ./eNPS " . $departmentNameRu . "</h3></u>
        <h1>" . $eNPSCompany . "%/" . $eNPS . "%</h1>
    </div></div>";
    ${$departmentNameEn . "_prev_prev_avg_arr"} = [];
    foreach ($sum_arr as $key => $item) {
        $questionText = $questions[$key] ?? "Неизвестный вопрос"; // заменяем индекс на текст вопроса
        ${$departmentNameEn . "_prev_prev_avg_arr"}[$questionText] = round($item / $count * 100, 2);
    }
    ${$departmentNameEn . "chartData"};
    ${$departmentNameEn . "chartData"} = [
        "current" => [
            "ОВ" => $involvement_org,
            "ИВ" => $involvement_int,
            "ЭВ" => $involvement_emo
        ],
        "previous" => [
            "ОВ" => $involvement_org_prev,
            "ИВ" => $involvement_int_prev,
            "ЭВ" => $involvement_emo_prev
        ],
        "prev_previous" => [
            "ОВ" => $involvement_org_prev_prev,
            "ИВ" => $involvement_int_prev_prev,
            "ЭВ" => $involvement_emo_prev_prev
        ]
    ];
    echo '</div>
    <div class="graph' . $departmentNameEn . '1">
        <canvas id="chart' . $departmentNameEn . '1" width="100" height="50"></canvas>
    </div>
    <div class="graph' . $departmentNameEn . '2">
        <canvas id="chart' . $departmentNameEn . '2" width="100" height="50"></canvas>
    </div>
    <div class="graph' . $departmentNameEn . '3">
        <canvas id="chart' . $departmentNameEn . '3" width="100" height="50"></canvas>
    </div>';
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ' . $departmentNameEn . 'labels = ' . json_encode(array_keys(${$departmentNameEn . "_curr_avg_arr"})) . ';
            const ' . $departmentNameEn . 'chartData = ' . json_encode(${$departmentNameEn . "chartData"}) . ';
            const ' . $departmentNameEn . 'currentData = ' . json_encode(array_values(${$departmentNameEn . "_curr_avg_arr"})) . ';
            const ' . $departmentNameEn . 'previousData = ' . json_encode(array_values(${$departmentNameEn . "_prev_avg_arr"})) . ';
            const ' . $departmentNameEn . 'prevPreviousData = ' . json_encode(array_values(${$departmentNameEn . "_prev_prev_avg_arr"})) . ';
            // Предполагаем, что currentData и previousData имеют одинаковую длину
            let ' . $departmentNameEn . 'sortedData = ' . $departmentNameEn . 'labels.map((label, index) => ({
                label: label,
                ' . $departmentNameEn . 'currentValue: ' . $departmentNameEn . 'currentData[index],
                ' . $departmentNameEn . 'previousValue: ' . $departmentNameEn . 'previousData[index],
                ' . $departmentNameEn . 'prevPreviousValue: ' . $departmentNameEn . 'prevPreviousData[index],
            }));

            // Сортируем по текущим значениям в порядке убывания
            ' . $departmentNameEn . 'sortedData.sort((a, b) => b.' . $departmentNameEn . 'currentValue - a.' . $departmentNameEn . 'currentValue);

            // Создаем новые массивы для меток и данных
            const ' . $departmentNameEn . 'sortedLabels = ' . $departmentNameEn . 'sortedData.map(item => item.label);
            const ' . $departmentNameEn . 'sortedCurrentData = ' . $departmentNameEn . 'sortedData.map(item => item.' . $departmentNameEn . 'currentValue);
            const ' . $departmentNameEn . 'sortedPreviousData = ' . $departmentNameEn . 'sortedData.map(item => item.' . $departmentNameEn . 'previousValue);
            const ' . $departmentNameEn . 'sortedPrevPreviousData = ' . $departmentNameEn . 'sortedData.map(item => item.' . $departmentNameEn . 'prevPreviousValue);

            const ctx' . $departmentNameEn . '1 = document.getElementById("chart' . $departmentNameEn . '1").getContext("2d");
            const chart' . $departmentNameEn . '1 = new Chart(ctx' . $departmentNameEn . '1, {
                type: "bar",
                data: {
                    labels: ["ОВ", "ИВ", "ЭВ"], // Метки для каждого критерия
                    datasets: [
                        {
                            label: "Предпредыдущий период",
                            data: Object.values(' . $departmentNameEn . 'chartData.prev_previous),
                            backgroundColor: "#2E5B0B", // Цвет для предпредыдущего периода
                            borderColor: "#2E5B9B",
                            borderWidth: 1
                        },
                        {
                            label: "Предыдущий период",
                            data: Object.values(' . $departmentNameEn . 'chartData.previous),
                            backgroundColor: "#2E5B9B", // Цвет для предыдущего периода
                            borderColor: "#2E5B9B",
                            borderWidth: 1
                        },
                        {
                            label: "Текущий период",
                            data: Object.values(' . $departmentNameEn . 'chartData.current),
                            backgroundColor: "#999B9A", // Основной цвет столбцов для текущего периода
                            borderColor: "#999B9A",
                            borderWidth: 1
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
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
                            anchor: "end",
                            align: "top",
                            formatter: function(value) {
                                return value.toFixed(2); // Формат значений
                            },
                            color: "#333", // Цвет текста
                            font: {
                                weight: "bold"
                            }
                        }
                    },
                    barPercentage: 0.4, // Уменьшенная ширина столбцов
                    categoryPercentage: 0.8 // Плотное расположение столбцов
                },
                plugins: [ChartDataLabels] // Включаем плагин
            });

            const ctx' . $departmentNameEn . '2 = document.getElementById("chart' . $departmentNameEn . '2").getContext("2d");
            const chart' . $departmentNameEn . '2 = new Chart(ctx' . $departmentNameEn . '2, {
                type: "bar",
                data: {
                    labels: ' . $departmentNameEn . 'sortedLabels,
                    datasets: [{
                            label: "Текущий период",
                            data: ' . $departmentNameEn . 'sortedCurrentData,
                            backgroundColor: "#999B9A", // Корпоративный синий цвет
                            borderColor: "#999B9A",
                            borderWidth: 1,
                            barThickness: 10, // Уменьшаем ширину столбцов
                        },
                        {
                            label: "Предыдущий период",
                            data: ' . $departmentNameEn . 'sortedPreviousData,
                            backgroundColor: "#2E5B9B", // Серый цвет
                            borderColor: "#2E5B9B",
                            borderWidth: 1,
                            barThickness: 10, // Уменьшаем ширину столбцов
                        },
                        {
                            label: "Предпредыдущий период",
                            data: ' . $departmentNameEn . 'sortedPrevPreviousData,
                            backgroundColor: "#2E5B0B", // Серый цвет
                            borderColor: "#2E5B9B",
                            borderWidth: 1,
                            barThickness: 10, // Уменьшаем ширину столбцов
                        },
                    ],
                },
                options: {
                    indexAxis: "y", // Делаем столбцы горизонтальными
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: "Показатели (%)",
                            },
                        },
                        y: {
                            title: {
                                display: true,
                                text: "Вопросы",
                            },
                        },
                    },
                    plugins: {
                        legend: {
                            position: "top",
                        },
                        tooltip: {
                            enabled: false, // Отключаем подсказки
                        },
                        // Добавляем значения столбцов справа
                        datalabels: {
                            anchor: "end",
                            align: "end",
                            formatter: (value) => `${value}%`, // Форматируем отображаемое значение
                            color: "#000", // Цвет текста
                        },
                    },
                },
                plugins: [ChartDataLabels] // Убедитесь, что у вас подключен плагин datalabels
            });

            const ctx' . $departmentNameEn . '3 = document.getElementById("chart' . $departmentNameEn . '3").getContext("2d");
            const labels = ' . json_encode($filteredLabels) . ';
            const differences = ' . json_encode($filteredDifferences) . ';
            function generateDistinctColors(numColors) {
                const colors = [];
                const step = 360 / numColors; // Угол в цветовом круге между соседними цветами
                for (let i = 0; i < numColors; i++) {
                    const hue = (i * step) % 360; // Вычисляем оттенок
                    colors.push(`hsl(${hue}, 50%, 50%)`); // Цвет в формате HSL (оттенок, насыщенность, яркость)
                }
                return colors;
            }
            function shuffle(array) {
                for (let i = array.length - 1; i > 0; i--) {
                    const j = Math.floor(Math.random() * (i + 1)); // Случайный индекс от 0 до i
                    [array[i], array[j]] = [array[j], array[i]]; // Обмен значений
                }
                return array;
            }
            const questionColors = shuffle(generateDistinctColors(labels.length));
            // Сортировка данных по убыванию
            const sortedData = labels
                .map((label, index) => ({
                    label: label,
                    value: differences[index]
                }))
                .sort((a, b) => b.value - a.value); // Сортируем по значениям

            // Перестраиваем данные и метки
            const sortedLabels = sortedData.map(item => item.label);
            const sortedDifferences = sortedData.map(item => item.value);
            new Chart(ctx' . $departmentNameEn . '3, {
                type: "bar",
                data: {
                    labels: sortedLabels,
                    datasets: [{
                        label: "Разница в положительных ответах (%)",
                        data: sortedDifferences,
                        backgroundColor: questionColors,
                        borderColor: questionColors,
                        borderWidth: 1,
                    }]
                },
                options: {
                    indexAxis: "y",
                    scales: {
                        y: {
                            beginAtZero: false,
                            suggestedMin: Math.min(...sortedDifferences) - 5,
                            suggestedMax: Math.max(...sortedDifferences) + 5,
                            title: {
                                display: true,
                                text: "Разница (%)",
                            },
                        },
                        x: {
                            title: {
                                display: true,
                                text: "Вопросы",
                            },
                        }
                    },
                    plugins: {
                        legend: {
                            display: false,
                            position: "top",
                        },
                    }
                }
            });
        });
    </script>';
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Метрики</title>
</head>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chroma-js/2.4.2/chroma.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>


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


    .container {
        display: flex;
        flex-direction: row;
        justify-content: space-around;
    }

    .square-block {
        display: flex;
        flex-direction: row;
    }

    .square {
        margin-right: 10px;
        border: 3px solid #2E5B9B;
        display: flex;
        flex-direction: column;
        width: fit-content;
        padding: 15px;
        align-items: center;
    }

    .square * {
        font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        color: #2E5B9B;
    }
</style>

<body>
    <?php
    $departmentIds = [];
    $departmentNameRu = "Общий";
    $departmentNameEn = "all";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees);

    $departmentIds = [12, 13, 14];
    $departmentNameRu = "PMO";
    $departmentNameEn = "PMO";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees);

    $departmentIds = [2, 3, 4, 5, 6, 7];
    $departmentNameRu = "Производственный департамент";
    $departmentNameEn = "PrDep";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees);

    $departmentIds = [8, 9, 10];
    $departmentNameRu = "Департамент развития бизнеса и продуктов";
    $departmentNameEn = "BusProdDep";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees);

    $departmentIds = [15];
    $departmentNameRu = "Служба персонала";
    $departmentNameEn = "HR";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees);

    $departmentIds = [16];
    $departmentNameRu = "Служба бизнес-процессов";
    $departmentNameEn = "SBP";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees);

    $departmentIds = [2];
    $departmentNameRu = "Технический департамент";
    $departmentNameEn = "TecDep";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees);

    $departmentIds = [11];
    $departmentNameRu = "Департамент бизнес-анализа";
    $departmentNameEn = "DepBusAn";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees);

    $departmentIds = [17];
    $departmentNameRu = "Бэк-офис";
    $departmentNameEn = "Back";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees);

    $departmentIds = [18];
    $departmentNameRu = "Руководитель департамента/службы";
    $departmentNameEn = "supervisor";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees);

    $departmentIds = [1];
    $departmentNameRu = "Департамент инноваций";
    $departmentNameEn = "DepInn";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees);
    ?>
</body>
<script>


</script>

</html>