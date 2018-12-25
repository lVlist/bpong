<?php
require_once('../conf/dbconfig.php');
require_once('../func/func.php');
menu();
menuAdmin();
$login = getUserLogin();

echo "<div id='main'>";
if ($login != null){
    $games = $conn->query("SELECT * FROM games");
    echo "<table>";
    echo "<tr>
            <td>№</td>
            <td>Турнир</td>
            <td>Финал</td>
            <td>Дата</td>
    </tr>";
    $i = 1;
    foreach ($games as $value){
        $date = $value['date'];
        $date = date("d.m.Y", strtotime("$date"));
        echo "<tr>
        <td>".$i++."</td>
        <td><a href='qualification.php?id=".$value['id']."'>".$value['game']."</a></td>
        <td><a href='final.php?id=".$value['id']."'>перейти</a></td>
        <td>".$date."</td>
        </tr>";
    }
    echo "</table>";
}else{
    echo "Доступ запрещен!";
}
echo "</div>";