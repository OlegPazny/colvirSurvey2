<?php
    require_once "assets/api/db_connect.php";

    $deps=mysqli_query($db, "SELECT * FROM `departments`");
    $deps=mysqli_fetch_all($deps);

    $survey=mysqli_query($db, "SELECT * FROM `survey`");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
</head>
<body>
<div class="container mt-5">
    <!-- Начать новый опрос -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Управление опросом</h5>
        </div>
        <div class="card-body">
            <button id="startNewSurvey" class="btn btn-danger">Начать новый опрос</button>
        </div>
    </div>
    <!-- Название исследования -->
    <div class="card">
        <div class="card-header">
            <h5>Текущее исследование</h5>
        </div>
        <div class="card-body">
            <form id="surveyForm">
                <div class="form-group">
                    <label for="surveyTitle">Введите название исследования</label>
                    <input type="text" class="form-control" id="surveyTitle" name="surveyTitle" placeholder="Например: Опрос за 2024 год" <?php if(mysqli_num_rows($survey)>0){$survey=mysqli_fetch_assoc($survey); echo "value='".$survey['title']."'";}?> required>
                </div>
                <div class="form-group">
                    <label for="excelFile">Загрузите количество сотрудников</label>
                    <div class="d-flex flex-column">
                        <?php
                            foreach($deps as $dep) {
                                if($dep[0]==19){
                                    continue;
                                }
                                echo "<div class='d-flex flex-row align-items-center mb-2'>";
                                echo "<input type='number' class='form-control w-25 me-2' id='dep_amount' name='".$dep[0]."' value='".$dep[2]."'>";
                                echo "<label for='dep_amount'>".$dep[1]."</label>";
                                echo "</div>";
                            }
                        ?>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Сохранить</button>
            </form>
        </div>
    </div>
</div>

</body>
<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"
    integrity="sha384-0pUGZvbkm6XF6gxjEnlmuGrJXVbNuzT9qBBavbLwCsOGabYfZo0T0to5eqruptLy"
    crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
    crossorigin="anonymous"></script>
    <script src="assets/js/admin.js"></script>
</html>