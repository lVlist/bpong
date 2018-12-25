<?php
require_once('../conf/dbconfig.php');
require_once('../func/func.php');
menu();
menuAdmin();
$login = getUserLogin();


$id_last_game = $conn -> query ("SELECT games.id FROM games ORDER BY games.id DESC LIMIT 1");
$last_game = $id_last_game->fetch_assoc();
		
//получения id игры методом GET если нету последний id из БД
    if (isset($_GET['id'])){
		$id_game = (int)$_GET['id'];
    }else{
		$id_game = (int)$last_game['id'];
    }

/* Общая таблица 3 туров */
$qualification = $conn->query("SELECT teams.team, qualification.r1, qualification.r2, qualification.r3, qualification.result, qualification.difference, games.game FROM qualification
                            INNER JOIN teams ON teams.id = qualification.id_team INNER JOIN games ON games.id = qualification.id_game
                            WHERE qualification.id_game = $id_game
                            ORDER BY qualification.result DESC, qualification.difference DESC");

/* Получаем название турнира */
$game = $qualification->fetch_assoc();
$game = $game['game'];

echo "<div id='main'>";
if ($login != null){
/* Общая таблица 3 туров */
echo "<div id='block'>";
echo "<table>";
echo "<h3>Турнир: ".$game."  <a href='create.php?id=".$id_game."'><img width='16px' src='http://".$_SERVER['HTTP_HOST']."/img/edit.png'></a></h3>";
echo "<tr align ='center'>";
echo "<td>№</td><td>Команда</td><td>Тур 1</td><td>Тур 2</td><td>Тур 3</td><td>Итого</td><td>Разница</td>";
echo "</tr>";

$i = 1;
foreach ($qualification as $value)
{
    echo "<tr align = 'center'>";
        echo "<td>".$i++."</td>";
        echo "<td>".$value['team']."</td>";
        winLoseView($value['r1']);
        winLoseView($value['r2']);
        winLoseView($value['r3']);
        echo "<td>".$value['result']."</td>";
        echo "<td>".$value['difference']."</td>";
    echo "</tr>";
}
echo "</table><br>";
echo "Выбрать:<a href='limit.php?limit=8&id_game=$id_game'> 8 |</a>";
echo "<a href='limit.php?limit=12&id_game=$id_game'> 12 |</a>";
echo "<a href='limit.php?limit=16&id_game=$id_game'> 16 |</a>";
echo "<a href='limit.php?limit=24&id_game=$id_game'> 24 |</a>";
echo "<a href='limit.php?limit=32&id_game=$id_game'> 32</a> команды для финала<br>";
echo "</div>";



/* Выводим 3 тура */
echo "<div id='block'>";
    roundGame(1);
    roundGame(2);
    roundGame(3);
echo "</div'>";
}else{
    echo "Доступ запрещен!";
}
echo "</div'>";