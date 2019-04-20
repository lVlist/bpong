<?php
require_once('../conf/dbconfig.php');
require_once('../func/func.php');
require_once('../func/header.php');
menu();
menuAdmin();
$login = getUserLogin();

echo "<div id='main'>";
if ($login != null){
    $games = $conn->query("SELECT * FROM score ORDER BY place ASC");
    echo "<h3>Очки:</h3>";
    echo "<form action='http://".$_SERVER['HTTP_HOST']."/func/score.php' method='POST'>";
    echo "<table>";
    echo "<tr align='center'>
        <td>Место</td>
        <td>Суббота</td>
        <td>Четверг</td>
        <td>King</td>
        <td>Queen</td>
        </tr>";

    foreach ($games as $value){
        echo "<tr align='center'>
        <td>".$value['place']."</td>
        <input type='hidden' name='".$value['place']."' value='".$value['place']."'>
        <td><input class='form-score' type='number' name='sat".$value['place']."' value='".$value['sat']."'></td>
        <td><input class='form-score' type='number' name='thu".$value['place']."' value='".$value['thu']."'></td>
        <td><input class='form-score' type='number' name='king".$value['place']."' value='".$value['king']."'></td>
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