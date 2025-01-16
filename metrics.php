<?php
require_once "assets/api/db_connect.php";

$data = mysqli_query($db, "SELECT * FROM `results`");
$data = mysqli_fetch_all($data);

$data_prev = mysqli_query($db, "SELECT * FROM `prev_results`");
$data_prev = mysqli_fetch_all($data_prev);

$data_prev_prev = mysqli_query($db, "SELECT * FROM `prev_prev_results`");
$data_prev_prev = mysqli_fetch_all($data_prev_prev);

$employees = mysqli_query($db, "SELECT * FROM departments;");
$employees = mysqli_fetch_all($employees);

$survey_titles=mysqli_query($db, "SELECT * FROM `survey`");
$survey_titles=mysqli_fetch_assoc($survey_titles);

$query = "
        SELECT 
            CASE
                WHEN d.id IN (3, 4, 5, 6, 7) THEN 'Производственный департамент'
                WHEN d.id IN (8, 9, 10) THEN 'Департамент развития бизнеса и продуктов'
                WHEN d.id IN (12, 13) THEN 'Проектный офис - Менеджеры'
                ELSE d.name
            END AS department_name,
            SUM(d.current) AS total_employees_current,
            SUM(d.prev) AS total_employees_prev,
            SUM((SELECT COUNT(*) FROM results r WHERE r.q1 = d.id)) AS current_participants,
            SUM((SELECT COUNT(*) FROM prev_results pr WHERE pr.q1 = d.id)) AS previous_participants
        FROM 
            departments d
            WHERE d.id != 19
        GROUP BY 
            CASE
                WHEN d.id IN (3, 4, 5, 6, 7) THEN 'Производственный департамент'
                WHEN d.id IN (8, 9, 10) THEN 'Департамент развития бизнеса и продуктов'
                WHEN d.id IN (12, 13) THEN 'Проектный офис - Менеджеры'
                ELSE d.name
            END;
";

$result = $db->query($query);

// Преобразуем результат в массив для передачи в функцию
$percentages = [];
while ($row = $result->fetch_assoc()) {
    $percentages[] = $row;
}

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
    20 => "Руководитель ценит мои заслуги, отмечает успехи (непосредственный руководитель, например TeamLeader для программиста, Специалиста по тестированию и тд)",
    21 => "Мой непосредственный руководитель и коллеги заинтересованы в том, чтобы я работал лучше",
    22 => "Ко мне часто обращаются за советом коллеги и\или руководитель",
    23 => "Я обучаюсь в процессе работы, узнаю много нового, мне помогают справиться с интересными задачами",
    24 => "Я понимаю, что моя работа важна для других и доволен, что тружусь в компании"
];
function roundToNearestFive($number)
{
    return round($number / 5) * 5;
}
function generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees, $percentages)
{
    global $db;

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
    $total_ov_arr = []; //организационное ОВ компания
    $total_iv_arr = []; //интеллектуальное ИВ компания
    $total_ev_arr = []; //эмоциональное ЭВ компания
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
            if ($i == 3 || $i == 10 || $i == 11 || $i == 15 || $i == 16 || $i == 17 || $i == 21 || $i == 24) {
                $total_ov_arr[$i] += $item[$i];
            }
            if ($i == 4 || $i == 8 || $i == 13 || $i == 14 || $i == 18 || $i == 22 || $i == 23) {
                $total_iv_arr[$i] += $item[$i];
            }
            if ($i == 5 || $i == 6 || $i == 7 || $i == 9 || $i == 12 || $i == 19 || $i == 20) {
                $total_ev_arr[$i] += $item[$i];
            }
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
        $totalEmployeesCompany += $employee[2];
    }
    foreach ($employees as $employee) {
        if (in_array($employee[0], $departmentIds)) {
            $totalEmployees += $employee[2];
        }
    }
    $participationRateCompany = $totalEmployeesCompany > 0 ? round(($participantsCompany / $totalEmployeesCompany) * 100, 2) : 0;
    $participationRate = $totalEmployees > 0 ? round(($participants / $totalEmployees) * 100, 2) : 0;
    $people_amount = $count; //КР количество респондентов
    $company_people_amount = $total_count; //КР количество респондентов
    //вовлеченность по всей компании
    $questions_count_total_company = count($total_sum_arr);
    $total_score_company = array_sum($total_sum_arr);
    $involvement_total_company = $total_count > 0 ? round($total_score_company * 100 / $questions_count_total_company / $total_count, 2) : 0;
    //вовлеченность общая ВО=СБ*100/(МБ*КР)
    $questions_count_total = count($sum_arr); //МБ максимальный балл (в строке)

    $total_score = array_sum($sum_arr); //СБ сумма баллов

    $max_score = $questions_count_total * $count;
    $involved = round(100 * $total_score / $max_score, 3);
    $involvement_total = round($total_score * 100 / $questions_count_total / $people_amount, 2);
    //вовлеченность организационная ОВ=СБ*100/(МБ/КР)
    $total_score_ov = array_sum($ov_arr); //СБ сумма баллов
    $company_total_score_ov = array_sum($total_ov_arr); //СБ сумма баллов компания

    $questions_count_ov = count($ov_arr); //МБ максимальный балл (в строке)
    $company_questions_count_ov = count($total_ov_arr); //МБ максимальный балл (в строке) компания
    //echo ("МБ " . $questions_count_ov . "<br>");
    $involvement_org = round($total_score_ov * 100 / $questions_count_ov / $people_amount, 2); //ТЕКУЩЕЕ ПО ДЕПАРТАМЕНТУ
    $company_involvement_org = round($company_total_score_ov * 100 / $company_questions_count_ov / $company_people_amount, 2); //ТЕКУЩЕЕ ПО КОМПАНИИ

    //вовлеченность интеллектуальная ИВ=СБ*100/(МБ*КР)
    $total_score_iv = array_sum($iv_arr); //СБ сумма баллов
    $company_total_score_iv = array_sum($total_iv_arr); //СБ сумма баллов компания

    $questions_count_iv = count($iv_arr); //МБ максимальный балл (в строке)
    $company_questions_count_iv = count($total_iv_arr); //МБ максимальный балл (в строке) компания

    $involvement_int = round($total_score_iv * 100 / $questions_count_iv / $people_amount, 2); //ТЕКУЩЕЕ ПО ДЕПАРТАМЕНТУ
    $company_involvement_int = round($company_total_score_iv * 100 / $company_questions_count_iv / $company_people_amount, 2); //ТЕКУЩЕЕ ПО КОМПАНИИ

    //вовлеченность эмоциональная ЭВ=СБ*100/(МБ*КР)
    $total_score_ev = array_sum($ev_arr); //СБ сумма баллов
    $company_total_score_ev = array_sum($total_ev_arr); //СБ сумма баллов

    $questions_count_ev = count($ev_arr); //МБ максимальный балл (в строке)
    $company_questions_count_ev = count($total_ev_arr); //МБ максимальный балл (в строке)

    $involvement_emo = round($total_score_ev * 100 / $questions_count_ev / $people_amount, 2); //ТЕКУЩЕЕ ПО ДЕПАРТАМЕНТУ
    $company_involvement_emo = round($company_total_score_ev * 100 / $company_questions_count_ev / $company_people_amount, 2); //ТЕКУЩЕЕ ПО КОМПАНИИ

    ${$departmentNameEn . "_curr_avg_arr"} = [];
    foreach ($sum_arr as $key => $item) {
        $questionText = $questions[$key] ?? "Неизвестный вопрос"; // заменяем индекс на текст вопроса
        ${$departmentNameEn . "_curr_avg_arr"}[$questionText] = round($item / $count * 100, 2);
    }
    $company_curr_avg_arr = [];
    foreach ($total_sum_arr as $key => $item) {
        $questionText = $questions[$key] ?? "Неизвестный вопрос"; // заменяем индекс на текст вопроса
        $company_curr_avg_arr[$questionText] = round($item / $total_count * 100, 2);
    }
    //eNPS
    $promouter_percent = round($promouters / $people_amount * 100, 2);
    $critics_percent = round($critics / $people_amount * 100, 2);
    $eNPS = $promouter_percent - $critics_percent;
    $eNPSCompany = $total_count > 0 ? round((($total_promouters - $total_critics) / $total_count) * 100, 2) : 0;
    //echo '</div><div class="second"><h3>Предыдущий период</h3>';
    $sum_arr = []; //общее
    $ov_arr = []; //организационное ОВ
    $iv_arr = []; //интеллектуальное ИВ
    $ev_arr = []; //эмоциональное ЭВ
    $previousPositiveRates = []; //положительные ответы за предыдущий период
    $differences = [];
    $count = 0;
    $total_promouters_prev = 0; //промоутеры
    $total_critics_prev = 0; //критики
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
            $total_promouters_prev++;
        } elseif ($item[12] <= 0.6) {
            $total_critics_prev++;
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
                            $promouters_prev++;
                        } elseif ($item[$i] >= 0 && $item[$i] <= 0.6) {
                            $critics_prev++;
                        }
                    }
                }
            }
            $count++;
        }
    }
    $people_amount = $count; //КР количество респондентов
    //echo ("КР " . $people_amount . "<br>");
    $questions_count_total_company_prev = count($total_sum_arr_prev);
    $total_score_company_prev = array_sum($total_sum_arr_prev);
    $involvement_total_company_prev = $total_count_prev > 0 ? round($total_score_company_prev * 100 / $questions_count_total_company_prev / $total_count_prev, 2) : 0;
    //вовлеченность общая ВО=СБ*100/(МБ*КР)
    $questions_count_total = count($sum_arr); //МБ максимальный балл (в строке)
    //echo ("МБ " . $questions_count_total . "<br>");
    $total_score = array_sum($sum_arr); //СБ сумма баллов
    //echo ("СБ " . $total_score . "<br>");
    $max_score = $questions_count_total * $count;
    $involved = round(100 * $total_score / $max_score, 3);
    $involvement_total_prev = round($total_score * 100 / $questions_count_total / $people_amount, 2);
    //echo ("ВО " . $involvement_total_prev . "<br>");
    //echo ("___________<br>");
    //вовлеченность организационная ОВ=СБ*100/(МБ/КР)
    $total_score_ov = array_sum($ov_arr); //СБ сумма баллов
    //echo ("СБ " . $total_score_ov . "<br>");
    $questions_count_ov = count($ov_arr); //МБ максимальный балл (в строке)
    //echo ("МБ " . $questions_count_ov . "<br>");
    $involvement_org_prev = round($total_score_ov * 100 / $questions_count_ov / $people_amount, 2);
    //echo ("ОВ " . $involvement_org_prev . "<br>");
    //echo ("___________<br>");
    //вовлеченность интеллектуальная ИВ=СБ*100/(МБ*КР)
    $total_score_iv = array_sum($iv_arr); //СБ сумма баллов
    //echo ("СБ " . $total_score_iv . "<br>");
    $questions_count_iv = count($iv_arr); //МБ максимальный балл (в строке)
    //echo ("МБ " . $questions_count_iv . "<br>");
    $involvement_int_prev = round($total_score_iv * 100 / $questions_count_iv / $people_amount, 2);
    //echo ("ИВ " . $involvement_int_prev . "<br>");
    //echo ("___________<br>");
    //вовлеченность эмоциональная ЭВ=СБ*100/(МБ*КР)
    $total_score_ev = array_sum($ev_arr); //СБ сумма баллов
    //echo ("СБ " . $total_score_iv . "<br>");
    $questions_count_ev = count($ev_arr); //МБ максимальный балл (в строке)
    //echo ("МБ " . $questions_count_ev . "<br>");
    $involvement_emo_prev = round($total_score_ev * 100 / $questions_count_ev / $people_amount, 2);
    //echo ("ЭВ " . $involvement_emo_prev . "<br>");
    //echo ("<br>");
    ${$departmentNameEn . "_prev_avg_arr"} = [];
    foreach ($sum_arr as $key => $item) {
        $questionText = $questions[$key] ?? "Неизвестный вопрос"; // заменяем индекс на текст вопроса
        ${$departmentNameEn . "_prev_avg_arr"}[$questionText] = round($item / $count * 100, 2);
    }
    $eNPS_prev = $promouter_percent - $critics_percent;
    $eNPSCompany_prev = $total_count_prev > 0 ? round((($total_promouters_prev - $total_critics_prev) / $total_count_prev) * 100, 2) : 0;
    //echo '</div><div class="third"><h3>Предпредыдущий период</h3>';
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
    //echo ("КР " . $people_amount . "<br>");
    //вовлеченность общая ВО=СБ*100/(МБ*КР)
    $questions_count_total = count($sum_arr); //МБ максимальный балл (в строке)
    //echo ("МБ " . $questions_count_total . "<br>");
    $total_score = array_sum($sum_arr); //СБ сумма баллов
    //echo ("СБ " . $total_score . "<br>");
    $max_score = $questions_count_total * $count;
    $involved = round(100 * $total_score / $max_score, 3);
    $involvement_total = round($total_score * 100 / $questions_count_total / $people_amount, 2);
    //echo ("ВО " . $involvement_total . "<br>");
    //echo ("___________<br>");
    //вовлеченность организационная ОВ=СБ*100/(МБ/КР)
    $total_score_ov = array_sum($ov_arr); //СБ сумма баллов
    //echo ("СБ " . $total_score_ov . "<br>");
    $questions_count_ov = count($ov_arr); //МБ максимальный балл (в строке)
    //echo ("МБ " . $questions_count_ov . "<br>");
    $involvement_org_prev_prev = round($total_score_ov * 100 / $questions_count_ov / $people_amount, 2);
    //echo ("ОВ " . $involvement_org_prev_prev . "<br>");
    //echo ("___________<br>");
    //вовлеченность интеллектуальная ИВ=СБ*100/(МБ*КР)
    $total_score_iv = array_sum($iv_arr); //СБ сумма баллов
    //echo ("СБ " . $total_score_iv . "<br>");
    $questions_count_iv = count($iv_arr); //МБ максимальный балл (в строке)
    //echo ("МБ " . $questions_count_iv . "<br>");
    $involvement_int_prev_prev = round($total_score_iv * 100 / $questions_count_iv / $people_amount, 2);
    //echo ("ИВ " . $involvement_int_prev_prev . "<br>");
    //echo ("___________<br>");
    //вовлеченность эмоциональная ЭВ=СБ*100/(МБ*КР)
    $total_score_ev = array_sum($ev_arr); //СБ сумма баллов
    //echo ("СБ " . $total_score_iv . "<br>");
    $questions_count_ev = count($ev_arr); //МБ максимальный балл (в строке)
    //echo ("МБ " . $questions_count_ev . "<br>");
    $involvement_emo_prev_prev = round($total_score_ev * 100 / $questions_count_ev / $people_amount, 2);
    // echo ("ЭВ " . $involvement_emo_prev_prev . "<br>");
    // echo ("<br>");

    if ($departmentIds == []) {
        //вычисление процента участия по департаментам
        // Подготовка данных для графика
        $departments = [];
        $currentPercentages = [];
        $previousPercentages = [];
        foreach ($percentages as $row) {
            $departments[] = $row['department_name'];
            $totalEmployees_current = (int)$row['total_employees_current'];
            $totalEmployees_prev = (int)$row['total_employees_prev'];
            $currentParticipants = (int)$row['current_participants'];
            $previousParticipants = (int)$row['previous_participants'];

            // Вычисляем проценты участия
            $currentPercentages[] = $totalEmployees_current > 0 ? round(($currentParticipants / $totalEmployees_current) * 100, 2) : 0;
            $previousPercentages[] = $totalEmployees_prev > 0 ? round(($previousParticipants / $totalEmployees_prev) * 100, 2) : 0;
        }
    }



    // Вычисление разницы положительных ответов
    foreach ($questions as $questionId => $questionText) {
        $currentRate = $currentPositiveRates[$questionId] ?? 0;
        $previousRate = $previousPositiveRates[$questionId] ?? 0;
        $differences[$questionId] = round($currentRate - $previousRate, 2);
    }
    $differences = array_values($differences);
    // // Округляем разницу до ближайшего числа, кратного 5
    // $differences = array_map('roundToNearestFive', $differences);
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
    echo '<div class="accordion-item">
                <h2 class="accordion-header" id="heading' . $departmentNameEn . '">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse' . $departmentNameEn . '" aria-expanded="true" aria-controls="collapse' . $departmentNameEn . '">
                        ' . $departmentNameRu . '
                    </button>
                </h2>
                <div id="collapse' . $departmentNameEn . '" class="accordion-collapse collapse" aria-labelledby="heading' . $departmentNameEn . '" data-bs-parent="#accordionExample">
                    <div class="accordion-body">';
    $survey_titles=mysqli_query($db, "SELECT * FROM `survey`");
    $survey_titles=mysqli_fetch_assoc($survey_titles);
    echo "<div class='square-block'><div class='square'>
        <u><h3>Вовлеченность по компании (".$survey_titles['title_current']."/".$survey_titles['title_prev'].")</h3></u>
        <h1>" . $involvement_total_company . "%/" . $involvement_total_company_prev . "%</h1>
    </div>";
    if (count($departmentIds) > 0) {
        echo "<div class='square'>
            <u><h3>Вовлеченность " . $departmentNameRu . "  (".$survey_titles['title_current']."/".$survey_titles['title_prev'].")</h3></u>
            <h1>" . $involvement_total . "%/" . $involvement_total_prev . "%</h1>
        </div>";
    }
    echo "<div class='square'>
        <u><h3>Участники по компании (кол-во/процент)</h3></u>
        <h1>" . $participantsCompany . "/" . $participationRateCompany . "%</h1>
    </div>";
    if (count($departmentIds) > 0) {
        echo "<div class='square'>
            <u><h3>Участники по департаменту (кол-во/процент)</h3></u>
            <h1>" . $participants . "/" . $participationRate . "%</h1>
        </div>";
    }
    if (count($departmentIds) > 0) {
        echo "<div class='square'>
        <u><h3>eNPS общ./eNPS " . $departmentNameRu . "</h3></u>
        <h1>" . $eNPSCompany . "%/" . $eNPS . "%</h1>
    </div>";
    } else {
        echo "<div class='square'>
        <u><h3>eNPS ".$survey_titles['title_current']."/eNPS ".$survey_titles['title_prev']."</h3></u>
        <h1>" . $eNPSCompany . "%/" . $eNPSCompany_prev . "%</h1>
    </div>";
    }
    echo '</div>';
    ${$departmentNameEn . "_prev_prev_avg_arr"} = [];
    foreach ($sum_arr as $key => $item) {
        $questionText = $questions[$key] ?? "Неизвестный вопрос"; // заменяем индекс на текст вопроса
        ${$departmentNameEn . "_prev_prev_avg_arr"}[$questionText] = round($item / $count * 100, 2);
    }
    ${$departmentNameEn . "chartData"};
    ${$departmentNameEn . "chartData"} = [
        "current" => [
            "ov" => $involvement_org,
            "iv" => $involvement_int,
            "ev" => $involvement_emo
        ],
        "previous" => [
            "ov" => $involvement_org_prev,
            "iv" => $involvement_int_prev,
            "ev" => $involvement_emo_prev
        ],
        "prev_previous" => [
            "ov" => $involvement_org_prev_prev,
            "iv" => $involvement_int_prev_prev,
            "ev" => $involvement_emo_prev_prev
        ]
    ];
    ${$departmentNameEn . "chartDataDepComp"};
    ${$departmentNameEn . "chartDataDepComp"} = [
        "company" => [
            "Компания ОВ" => $company_involvement_org,
            "Компания ИВ" => $company_involvement_int,
            "Компания ЭВ" => $company_involvement_emo
        ],
        "department" => [
            "ОВ" => $involvement_org,
            "ИВ" => $involvement_int,
            "ЭВ" => $involvement_emo
        ]
    ];
    echo '
    <div class="graph' . $departmentNameEn . '1">
        <canvas id="chart' . $departmentNameEn . '1" width="100" height="50"></canvas>
    </div>';
    if (count($departmentIds) > 0) {
        $departmentIds_str = implode(",", $departmentIds);
        $fill_data = mysqli_query($db, "SELECT * FROM `recommendations` WHERE `department_ids`='$departmentIds_str'");
        if (mysqli_num_rows($fill_data) > 0) {
            $fill_data = mysqli_fetch_assoc($fill_data);
            echo '
                <div class="card">
                    <div class="form-group card-body">
                        <p>
                            <b>Организационный</b> компонент вовлеченности - показывает, ощущают ли сотрудники сопричастность к компании, ее результатам и продуктам<br><b>Эмоциональный</b> компонент вовлеченности - определяет, какие эмоции, состояния вызывает у сотрудников рабочий процесс<br><b>Интеллектуальный</b> компонент вовлеченности - показывает, погружены ли сотрудники в задачи, увлечены ли их выполнением и уровнем сложности
                        </p>
                        <label for="conclusion">Вывод</label>
                        <input type="text" class="form-control" id="conclusion" name="conclusion" value="' . $fill_data['conclusion'] . '">
                        <label for="recommendations">Рекомендации</label>
                        <input type="text" class="form-control" id="recommendations" name="recommendations" value="' . $fill_data['recommendation'] . '">
                        <input type="button" class="btn btn-primary mt-3 save-btn" name="save" data-department-ids=' . $departmentIds_str . ' value="Сохранить"/>
                    </div>
                </div>
            ';
        } else {
            echo '
                <div class="card">
                    <div class="form-group card-body px-2">
                        <p>
                            Организационный компонент вовлеченности - показывает, ощущают ли сотрудники сопричастность к компании, ее результатам и продуктам<br>Эмоциональный компонент вовлеченности - определяет, какие эмоции, состояния вызывает у сотрудников рабочий процесс<br>Интеллектуальный компонент вовлеченности - показывает, погружены ли сотрудники в задачи, увлечены ли их выполнением и уровнем сложности
                        </p>
                        <label for="conclusion">Вывод</label>
                        <input type="text" class="form-control" id="conclusion" name="conclusion">
                        <label for="recommendations">Рекомендации</label>
                        <input type="text" class="form-control" id="recommendations" name="recommendations">
                        <input type="button" class="btn btn-primary mt-3 save-btn" name="save" data-department-ids=' . $departmentIds_str . ' value="Сохранить"/>
                    </div>
                </div>
            ';
        }
    }
    echo '<div class="graph' . $departmentNameEn . '2">
        <canvas id="chart' . $departmentNameEn . '2" width="100" height="50"></canvas>
    </div>';
    if (count($departmentIds) > 0) {
        echo '<div class="graph' . $departmentNameEn . '3">
            <canvas id="chart' . $departmentNameEn . '3" width="100" height="50"></canvas>
        </div>';
    } else {
        echo '<div class="graphPercentages">
            <canvas id="chartPercentages" width="400" height="400"></canvas>
        </div>';
    }
    echo '</div>
    <button class="btn btn-primary export-to-pdf mb-4 ms-4" data-target="#collapse' . $departmentNameEn . '">Выгрузить в PDF</button>
        </div>
        </div>';
    echo '
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ' . $departmentNameEn . 'labels = ' . json_encode(array_keys(${$departmentNameEn . "_curr_avg_arr"})) . ';
            const ' . $departmentNameEn . 'chartData = ' . json_encode(${$departmentNameEn . "chartData"}) . ';
            const ' . $departmentNameEn . 'chartDataDepComp = ' . json_encode(${$departmentNameEn . "chartDataDepComp"}) . ';
            const companyCurrentData=' . json_encode(array_values($company_curr_avg_arr)) . ';
            const ' . $departmentNameEn . 'currentData = ' . json_encode(array_values(${$departmentNameEn . "_curr_avg_arr"})) . ';
            const ' . $departmentNameEn . 'previousData = ' . json_encode(array_values(${$departmentNameEn . "_prev_avg_arr"})) . ';
            const ' . $departmentNameEn . 'prevPreviousData = ' . json_encode(array_values(${$departmentNameEn . "_prev_prev_avg_arr"})) . ';';
    if (count($departmentIds) > 0) {
        echo '
            // Предполагаем, что currentData и previousData имеют одинаковую длину
            let ' . $departmentNameEn . 'sortedData = ' . $departmentNameEn . 'labels.map((label, index) => ({
                label: label,
                companyCurrentValue: companyCurrentData[index],
                ' . $departmentNameEn . 'currentValue: ' . $departmentNameEn . 'currentData[index],
            }));

            // Сортируем по текущим значениям в порядке убывания
            ' . $departmentNameEn . 'sortedData.sort((a, b) => b.' . $departmentNameEn . 'currentValue - a.' . $departmentNameEn . 'currentValue);

            // Создаем новые массивы для меток и данных
            const ' . $departmentNameEn . 'sortedLabels = ' . $departmentNameEn . 'sortedData.map(item => item.label);
            const ' . $departmentNameEn . 'sortedCurrentData = ' . $departmentNameEn . 'sortedData.map(item => item.' . $departmentNameEn . 'currentValue);
            const companySortedCurrentData = ' . $departmentNameEn . 'sortedData.map(item => item.companyCurrentValue);';
    } else {
        echo '
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
    ';
    }
    if ($departmentIds == [18]) {
        echo '
        const ctx' . $departmentNameEn . '1 = document.getElementById("chart' . $departmentNameEn . '1").getContext("2d");
        const chart' . $departmentNameEn . '1 = new Chart(ctx' . $departmentNameEn . '1, {
            type: "bar",
            data: {
                labels: ["ОВ", "ИВ", "ЭВ"],
                datasets: [
                    {
                        label: "Руководители",
                        data: Object.values(' . $departmentNameEn . 'chartDataDepComp.department),
                        backgroundColor: "#2E5B9B", // Цвет для департамента
                        borderColor: "#2E5B9B",
                        borderWidth: 0
                    },
                    {
                        label: "Компания",
                        data: Object.values(' . $departmentNameEn . 'chartDataDepComp.company),
                        backgroundColor: "#999B9A", // Основной цвет столбцов компании
                        borderColor: "#999B9A",
                        borderWidth: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: false,
                        },
                        ticks: {
                            display: false,
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                        }
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
            plugins: [
                ChartDataLabels,
                {
                    id: "corsFix",
                    beforeDraw: (chart) => {
                        const canvas = chart.canvas;
                        canvas.crossOrigin = "anonymous"; // Настройка crossOrigin
                    }
                }    
            ] // Включаем плагин
        });';
    } else {
        echo '
        const ctx' . $departmentNameEn . '1 = document.getElementById("chart' . $departmentNameEn . '1").getContext("2d");
        const chart' . $departmentNameEn . '1 = new Chart(ctx' . $departmentNameEn . '1, {
            type: "bar",
            data: {
                labels: ["'.$survey_titles['title_prev_prev'].'", "'.$survey_titles['title_prev'].'", "'.$survey_titles['title_current'].'"], // Метки для каждого критерия
                datasets: [
                    {
                        label: "ОВ",
                        data: [
                            ' . $departmentNameEn . 'chartData.prev_previous.ov,
                            ' . $departmentNameEn . 'chartData.previous.ov,
                            ' . $departmentNameEn . 'chartData.current.ov
                        ],
                        backgroundColor: "#6e9cde", // Цвет для предпредыдущего периода
                        borderColor: "#6e9cde",
                        borderWidth: 0
                    },
                    {
                        label: "ИВ",
                        data: [
                            ' . $departmentNameEn . 'chartData.prev_previous.iv,
                            ' . $departmentNameEn . 'chartData.previous.iv,
                            ' . $departmentNameEn . 'chartData.current.iv
                        ],
                        backgroundColor: "#2E5B9B", // Цвет для предыдущего периода
                        borderColor: "#2E5B9B",
                        borderWidth: 0
                    },
                    {
                        label: "ЭВ",
                        data: [
                            ' . $departmentNameEn . 'chartData.prev_previous.ev,
                            ' . $departmentNameEn . 'chartData.previous.ev,
                            ' . $departmentNameEn . 'chartData.current.ev
                        ],
                        backgroundColor: "#999B9A", // Основной цвет столбцов для текущего периода
                        borderColor: "#999B9A",
                        borderWidth: 0
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: false,
                        },
                        ticks: {
                            display: false,
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                        }
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
                        color: "#d0d0d0", // Цвет текста
                        font: {
                            weight: "bold"
                        }
                    }
                },
                barPercentage: 0.4, // Уменьшенная ширина столбцов
                categoryPercentage: 0.8 // Плотное расположение столбцов
            },
            plugins: [
                ChartDataLabels,
                {
                    id: "corsFix",
                    beforeDraw: (chart) => {
                        const canvas = chart.canvas;
                        canvas.crossOrigin = "anonymous"; // Настройка crossOrigin
                    }
                }    
            ] // Включаем плагин
        });';
    }

    echo 'const ctx' . $departmentNameEn . '2 = document.getElementById("chart' . $departmentNameEn . '2").getContext("2d");
        const departmentData = ' . $departmentNameEn . 'sortedCurrentData;
        // Генерация цветов для текста вопросов
        const questionColors' . $departmentNameEn . ' = departmentData.map(value => value < 70 ? "red" : "#000");
        const chart' . $departmentNameEn . '2 = new Chart(ctx' . $departmentNameEn . '2, {
                type: "bar",
                data: {
                    labels: ' . $departmentNameEn . 'sortedLabels,';
    if (count($departmentIds) > 0) {
        echo 'datasets: [{
                            label: "Компания",
                            data: companySortedCurrentData,
                            backgroundColor: "#999B9A", // Корпоративный синий цвет
                            borderColor: "#999B9A",
                            borderWidth: 1,
                            barThickness: 10, // Уменьшаем ширину столбцов
                        },
                        {
                            label: "' . $departmentNameRu . '",
                            data: ' . $departmentNameEn . 'sortedCurrentData,
                            backgroundColor: "#2E5B9B", // Серый цвет
                            borderColor: "#2E5B9B",
                            borderWidth: 1,
                            barThickness: 10, // Уменьшаем ширину столбцов
                        }
                    ],
                    },';
    } else {
        echo 'datasets: [{
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
                        }
                    ],
                    },';
    }
    echo '      options: {
                    indexAxis: "y",
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: {
                                display: false,
                                text: "Показатели (%)",
                            },
                            grid: {
                                display: false,
                            },
                            ticks: {
                                display: false,
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            },
                            title: {
                                display: false,
                                text: "Вопросы",
                            },
                            ticks: {
                                font: {
                                    size: 10, // Уменьшенный шрифт для меток
                                },
                                color: function(context) {
                                    // Установка цвета текста
                                    const index = context.index;
                                    return questionColors' . $departmentNameEn . '[index] || "#d0d0d0";
                                },
                                callback: function(value, index) {
                                    const label = this.getLabelForValue(value);
                                    const maxLineLength = 100; // Максимальная длина строки
                                    const words = label.split(" ");
                                    let currentLine = "";
                                    const lines = [];

                                    words.forEach(word => {
                                        if ((currentLine + word).length > maxLineLength) {
                                            lines.push(currentLine.trim());
                                            currentLine = word + " ";
                                        } else {
                                            currentLine += word + " ";
                                        }
                                    });

                                    if (currentLine) lines.push(currentLine.trim());

                                    // Возвращаем текст с переносом строк
                                    return lines;
                                },
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
                        datalabels: {
                            anchor: "end",
                            align: "end",
                            formatter: (value) => `${value}%`,
                            color: "#d0d0d0",
                            font: {
                                weight: "bold"
                            }
                        },
                    },
                    layout: {
                        padding: {
                            right: 50, // Добавляем отступ справа
                        },
                    },
                },
                plugins: [
                    ChartDataLabels,
                    {
                        id: "highlightLabels",
                        beforeDraw: (chart) => {
                            const ctx = chart.ctx;
                            const yAxis = chart.scales.y;

                            yAxis.ticks.forEach((tick, index) => {
                                const label = yAxis.getLabelForValue(tick.value);
                                const color = questionColors' . $departmentNameEn . '[index] || "#d0d0d0";

                                ctx.save();
                                ctx.fillStyle = color;
                                ctx.textAlign = "right";
                                ctx.textBaseline = "middle";
                                ctx.font = "10px";
                                ctx.fillText(label, yAxis.left - 10, yAxis.getPixelForTick(index));
                                ctx.restore();
                            });
                        },
                    },
                    {
                        id: "drawThresholdLine",
                        beforeDraw: (chart) => {
                            const ctx = chart.ctx;
                            const xScale = chart.scales.x;

                            // Координата на оси X для 70%
                            const thresholdX = xScale.getPixelForValue(70);

                            ctx.save();
                            ctx.beginPath();
                            ctx.moveTo(thresholdX, chart.chartArea.top); // Начало линии
                            ctx.lineTo(thresholdX, chart.chartArea.bottom); // Конец линии
                            ctx.strokeStyle = "#ff9d9d";
                            ctx.lineWidth = 2;
                            ctx.stroke();
                            ctx.restore();
                        },
                    },
                    {
                        id: "corsFix",
                        beforeDraw: (chart) => {
                            const canvas = chart.canvas;
                            canvas.crossOrigin = "anonymous"; // Настройка crossOrigin
                        }
                    }
                ],';
    echo '});';
    if (count($departmentIds) > 0) {
        echo '
        const ctx' . $departmentNameEn . '3 = document.getElementById("chart' . $departmentNameEn . '3").getContext("2d");
        const labels = ' . json_encode($filteredLabels) . ';
        const differences = ' . json_encode($filteredDifferences) . ';
        
        function generateDistinctColors(numColors) {
            // Основные цвета радуги в формате HSL (по порядку: красный, оранжевый, желтый, зеленый, голубой, синий, фиолетовый)
            const rainbowColors = [
                "hsl(0, 100%, 50%)",    // Красный
                "hsl(30, 100%, 50%)",   // Оранжевый
                "hsl(52, 100%, 50%)",   // Желтый
                "hsl(105, 100%, 34%)",  // Зеленый
                "hsl(202, 100%, 66%)",  // Голубой
                "hsl(240, 100%, 50%)",  // Синий
                "hsl(270, 100%, 50%)"   // Фиолетовый
            ];

            const colors = [];
            const rainbowCount = rainbowColors.length;

            for (let i = 0; i < numColors; i++) {
                // Индекс в массиве базовых цветов радуги
                const baseIndex = i % rainbowCount;

                // Номер цикла для увеличения яркости (каждый цикл добавляет более светлые оттенки)
                const brightnessShift = Math.floor(i / rainbowCount) * 20;

                // Получаем цвет из радуги и корректируем яркость
                const baseColor = rainbowColors[baseIndex];
                const match = baseColor.match(/hsl\((\d+), (\d+)%, (\d+)%\)/);

                if (match) {
                    const [_, hue, saturation, lightness] = match.map(Number);
                    const newLightness = Math.min(lightness + brightnessShift, 80); // Ограничиваем яркость до 80%
                    colors.push(`hsl(${hue}, ${saturation}%, ${newLightness}%)`);
                }
            }

            return colors;
        }

        
        const questionColors = generateDistinctColors(labels.length);
        
        const sortedData = labels
            .map((label, index) => ({
                label: label,
                value: differences[index]
            }))
            .sort((a, b) => b.value - a.value);
    
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
                    categoryPercentage: 1.0, // Убираем отступы между категориями
                    barPercentage: 1.0,
                }]
            },
            options: {
                indexAxis: "y",
                scales: {
                    y: {
                        grid: {
                            display: false,
                        },
                        beginAtZero: false,
                        suggestedMin: Math.min(...sortedDifferences) - 5,
                        suggestedMax: Math.max(...sortedDifferences) + 5,
                        title: {
                            display: false,
                            text: "Вопросы",
                        },
                        ticks: {
                            font: {
                                size: 10, // Устанавливаем шрифт 8px для labels 
                            },
                            color: "#000",
                            callback: function(value, index) {
                                const label = this.getLabelForValue(value);
                                // Разбиваем длинный текст на строки по пробелу
                                const maxLineLength = 120; // Максимальная длина строки
                                const words = label.split(" ");
                                let currentLine = "";
                                const lines = [];
                                words.forEach(word => {
                                    if ((currentLine + word).length > maxLineLength) {
                                        lines.push(currentLine.trim());
                                        currentLine = word + " ";
                                    } else {
                                        currentLine += word + " ";
                                    }
                                });
                                if (currentLine) lines.push(currentLine.trim());
                                return lines;
                            },
                        },
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            display: false,
                        },
                        title: {
                            display: false,
                            text: "Разница (%)",
                        },
                    }
                },
                plugins: {
                    tooltip: {
                        enabled: false // Отключаем tooltip
                    },
                    legend: {
                        display: false,
                    },
                },
                layout: {
                    padding: {
                        right: 50, // Добавляем отступ справа
                    },
                },
            },
            plugins: [
                {
                    id: "displayValues",
                    afterDatasetsDraw: (chart) => {
                        const ctx = chart.ctx;
                        chart.data.datasets[0].data.forEach((value, index) => {
                            const meta = chart.getDatasetMeta(0);
                            const bar = meta.data[index];
                            const xPos = bar.x;
                            const yPos = bar.y;
    
                            ctx.save();
                            ctx.font = "bold 10px Roboto"; // Шрифт 12px для процентов
                            ctx.fillStyle = "#d0d0d0"; // Чёрный цвет для текста процентов
                            ctx.textAlign = value < 0 ? "right" : "left";
                            
                            // Отображаем проценты рядом со столбцами
                            const offset = value < 0 ? -10 : 10; // Смещение для отрицательных и положительных значений
                            ctx.fillText(`${value}%`, xPos + offset, yPos + 4); // Смещаем текст вниз на 4px
                            ctx.restore();
                        });
                    }
                }
            ],
        });';
    } else {
        echo '
                const departmentLabels = ' . json_encode($departments) . ';
                const currentPercentages = ' . json_encode($currentPercentages) . ';
                const previousPercentages = ' . json_encode($previousPercentages) . ';
                const ctxPercentages = document.getElementById("chartPercentages").getContext("2d");

                new Chart(ctxPercentages, {
                    type: "bar",
                    data: {
                        labels: departmentLabels,
                        datasets: [
                            {
                                label: "Предыдущий период",
                                data: previousPercentages,
                                backgroundColor: "rgb(0, 88, 165)",
                                borderColor: "rgb(0, 88, 165)",
                                borderWidth: 0
                            },
                            {
                                label: "Текущий период",
                                data: currentPercentages,
                                backgroundColor: "#999B9A",
                                borderColor: "#999B9A",
                                borderWidth: 0
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: false,
                                    text: "Процент участия (%)"
                                },
                                grid: {
                                    display: false,
                                },
                                ticks: {
                                    display: false,
                                }
                            },
                            x: {
                                title: {
                                    display: false,
                                    text: "Подразделения"
                                },
                                grid: {
                                    display: false,
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: true
                            },
                            tooltip: {
                                enabled: true
                            },
                            datalabels: {
                                display: true,
                                color: "black",
                                anchor: "end", // Привязка лейбла к верхнему краю столбца
                                align: "top", // Лейбл будет над столбцом
                                formatter: function (value) {
                                    return value + "%"; // Добавляем знак процента
                                },
                                font: {
                                    size: 12
                                },
                                font: {
                                    weight: "bold"
                                },
                                offset: 5 // Отступ над столбцом
                            }
                        }
                    },
                    plugins: [
                        ChartDataLabels,
                        {
                            id: "corsFix",
                            beforeDraw: (chart) => {
                                const canvas = chart.canvas;
                                canvas.crossOrigin = "anonymous"; // Настройка crossOrigin
                            }
                        },
                    ]
                });
                ';
    }
    echo '});
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
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chroma-js/2.4.2/chroma.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dom-to-image/2.6.0/dom-to-image.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dom-to-image-more@2/dist/dom-to-image-more.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>


<style>
    @font-face {
        font-family: 'Roboto';
        src: url('assets/fonts/Roboto-Regular.ttf') format('truetype');
        font-weight: normal;
    }
    @font-face {
        font-family: 'Roboto';
        src: url('assets/fonts/Roboto-Bold.ttf') format('truetype');
        font-weight: bold;
    }
    * {
        font-family: Roboto !important;
    }

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
        text-align: center;
        justify-content: center;
    }
    .square h1{
        font-weight: bold !important;
    }
    .square * {
        font-family: Roboto;
        color: #2E5B9B;
    }
</style>
<div class="accordion" id="accordionExample">
    <?php
    $departmentIds = [];
    $departmentNameRu = "Общий";
    $departmentNameEn = "all";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees, $percentages);

    $departmentIds = [12, 13, 14];
    $departmentNameRu = "Проектный офис";
    $departmentNameEn = "PMO";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees, $percentages);

    $departmentIds = [12];
    $departmentNameRu = "Проектный офис - Менеджеры разработки";
    $departmentNameEn = "PMOdev";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees, $percentages);

    $departmentIds = [13];
    $departmentNameRu = "Проектный офис - Менеджеры внедрения";
    $departmentNameEn = "PMOimpl";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees, $percentages);

    $departmentIds = [14];
    $departmentNameRu = "Проектный офис - Отдел внедрения";
    $departmentNameEn = "PMOimpldep";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees, $percentages);

    $departmentIds = [2, 3, 4, 5, 6, 7];
    $departmentNameRu = "Производственный департамент";
    $departmentNameEn = "PrDep";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees, $percentages);

    $departmentIds = [8, 9, 10];
    $departmentNameRu = "Департамент развития бизнеса и продуктов";
    $departmentNameEn = "BusProdDep";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees, $percentages);

    $departmentIds = [15];
    $departmentNameRu = "Служба персонала";
    $departmentNameEn = "HR";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees, $percentages);

    $departmentIds = [16];
    $departmentNameRu = "Служба бизнес-процессов";
    $departmentNameEn = "SBP";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees, $percentages);

    $departmentIds = [2];
    $departmentNameRu = "Технический департамент";
    $departmentNameEn = "TecDep";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees, $percentages);

    $departmentIds = [11];
    $departmentNameRu = "Департамент бизнес-анализа";
    $departmentNameEn = "DepBusAn";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees, $percentages);

    $departmentIds = [17];
    $departmentNameRu = "Бэк-офис";
    $departmentNameEn = "Back";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees, $percentages);

    $departmentIds = [18];
    $departmentNameRu = "Руководитель департамента/службы";
    $departmentNameEn = "supervisor";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees, $percentages);

    $departmentIds = [1];
    $departmentNameRu = "Департамент инноваций";
    $departmentNameEn = "DepInn";
    generateDashData($data, $data_prev, $data_prev_prev, $departmentIds, $departmentNameRu, $departmentNameEn, $questions, $employees, $percentages);
    ?>
</div>
</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.4.0/jspdf.umd.min.js"></script>
<script type="module" src="assets/fonts/Roboto-normal.js"></script>
<script type="module" src="assets/js/export.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/print-js/1.6.0/print.js" integrity="sha512-/fgTphwXa3lqAhN+I8gG8AvuaTErm1YxpUjbdCvwfTMyv8UZnFyId7ft5736xQ6CyQN4Nzr21lBuWWA9RTCXCw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<!-- <script type="module" src="assets/js/export2.js"></script> -->
<script src="assets/js/save_rec.js"></script>

</html>