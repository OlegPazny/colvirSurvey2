<?php
require_once "db_connect.php";

$departments = mysqli_query($db, "SELECT * FROM `departments`");
$departments = mysqli_fetch_all($departments);
function matrixLine($description, $qnum)
{
    echo ('<tr>
                <td>' . $description . '</td>
                <td><input type="radio" name="q' . $qnum . '" value="NULL"></td>
                <td><input type="radio" name="q' . $qnum . '" value="0.00"></td>
                <td><input type="radio" name="q' . $qnum . '" value="0.50"></td>
                <td><input type="radio" name="q' . $qnum . '" value="1.00"></td>
            </tr>');
}
function radioLine($question, $qnum)
{
    echo ('<div class="mb-3">
                    <label class="form-label required">' . $question . '</label>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="q' . $qnum . '" id="yesq' . $qnum . '" value=1 required>
                        <label class="form-check-label" for="yesq' . $qnum . '">Да</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="q' . $qnum . '" id="noq' . $qnum . '" value=0 required>
                        <label class="form-check-label" for="noq' . $qnum . '">Нет</label>
                    </div>
                </div>');
}
