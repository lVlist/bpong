<?php
require_once('../conf/dbconfig.php');
require_once('../func/func.php');
require_once('../func/header.php');
menu();
menuAdmin();
$login = getUserLogin();

echo "<div id='main'>";
if ($login != null){
    $games = $conn->query("SELECT * FROM games");
    echo "<h3>Турниры:</h3>";
    echo "<table>";
    $i = 1;
    foreach ($games as $value){
        $date = $value['date'];
        $date = date("d.m.Y", strtotime("$date"));
        echo "<tr>
        <td>".$i++."</td>
        <td><a href='qualification.php?id=".$value['id']."'>".$value['game']."</a></td>
        <td><a href='final.php?id=".$value['id']."'>Финал</td>
        <td><a href='statistics.php?id=".$value['id']."'>Результаты турнира</td>
        <td>".$date."</td>
        </tr>";
    }
    echo "</table>";
}else{
    echo "Доступ запрещен!";
}
echo "</div>";