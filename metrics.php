<?php
require_once "assets/api/db_connect.php";

$data = mysqli_query($db, "SELECT * FROM `results`");
$data = mysqli_fetch_all($data);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Метрики</title>
</head>
<style>
    td,
    tr,
    th {
        border-bottom: 1px solid gray;
        border-right: 1px solid gray;
    }

    table {
        font-size: 10px;
    }
</style>

<body>
    <div class="container">
        <div class="table">
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
                    $sum_arr = [];
                    $count = 0;
                    foreach ($data as $item) {

                        echo "<tr>";
                        for ($i = 0; $i <= 24; $i++) {
                            echo ("<td>" . $item[$i] . "</td>");
                        }
                        for ($i = 3; $i <= 24; $i++) {
                            $sum_arr[$i] += $item[$i];
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
        </div>
        <div class="formula">
            <?php
            $questions_count = 22;
            $max_score=$questions_count*$count;
            $total_score=array_sum($sum_arr);
            $involved=round(100*$total_score/$max_score, 3);

            
            ?>
        </div>
    </div>
</body>

</html>