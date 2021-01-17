<?php
require_once('../conf/dbconfig.php');
require_once('../conf/config.php');
require_once('../func/func.php');
require_once('../func/header.php');
menu();
menuAdmin();
$login = getUserLogin();

echo "<h3>Очки:</h3>";

echo "<div id='main'>";
if ($login != null){
    $games = $conn->query("SELECT * FROM $dbt_score ORDER BY place ASC");

    echo "<form action='http://".$_SERVER['HTTP_HOST']."/func/score.php' method='POST'>";
    echo "<table>";
    echo "<tr align='center'>
        <td>Место</td>
        <td>Суббота</td>
        <td>Недельный</td>";
    echo $organization == "minsk" ? "<td>Old school</td>" : "<td>Личка</td>";
    echo "<td>King</td>
        <td>Queen</td>
        </tr>";

    foreach ($games as $value){
        echo "<tr align='center'>
        <td>".$value['place']."</td>
        <input type='hidden' name='".$value['place']."' value='".$value['place']."'>
        <td><input class='form-score' type='number' name='sat".$value['place']."' value='".$value['sat']."'></td>
        <td><input class='form-score' type='number' name='thu".$value['place']."' value='".$value['thu']."'></td>";

        if ($organization == "minsk"){
            echo "<td><input class='form-score' type='number' name='old".$value['place']."' value='".$value['old']."'></td>";
        }else{
            echo "<td><input class='form-score' type='number' name='personal".$value['place']."' value='".$value['personal']."'></td>";
        }

        echo "<td><input class='form-score' type='number' name='king".$value['place']."' value='".$value['king']."'></td>
        <td><input class='form-score' type='number' name='queen".$value['place']."' value='".$value['queen']."'></td>
        </tr>";
    }
        echo "</table>
        <center><input class ='submit -addteam' type='submit' value='ИЗМЕНИТЬ'></center>
                </form>";
}else{
    echo "Доступ запрещен!";
}
echo "</div>";