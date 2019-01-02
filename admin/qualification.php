<?php
require_once('../conf/dbconfig.php');
require_once('../func/func.php');
require_once('../func/header.php');
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
$qualification = 
    $conn->query("SELECT teams.team, qualification.r1, qualification.r2, qualification.r3, qualification.result, 
    qualification.difference, games.game FROM qualification
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
foreach ($qualification as $value){
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
echo "Выбрать:<a href='limit.php?limit=8&id=$id_game'> 8 |</a>";
echo "<a href='limit.php?limit=12&id=$id_game'> 12 |</a>";
echo "<a href='limit.php?limit=16&id=$id_game'> 16 |</a>";
echo "<a href='limit.php?limit=24&id=$id_game'> 24 |</a>";
echo "<a href='limit.php?limit=32&id=$id_game'> 32</a> команды для финала<br>";
echo "</div>";



/* Выводим 3 тура */

$sql = "SELECT t1.team t1, Q.s1, Q.s2, t2.team t2, Q.round,t1.id AS id_t1, t2.id AS id_t2, Q.id AS id_match 
FROM q_games Q
JOIN qualification AS id_t1 ON id_t1.key_team = Q.key_team1
JOIN qualification AS id_t2 ON id_t2.key_team = Q.key_team2
JOIN teams t1 ON t1.id = id_t1.id_team
JOIN teams t2 ON t2.id = id_t2.id_team
WHERE id_t1.id_game = $id_game AND id_t2.id_game = $id_game AND Q.id_game = $id_game";

/* Выборка 3 туров */
$q_game = $conn->query($sql);

echo "<div id='block'>";

for($t=1;$t<=3;$t++){
    echo "<h3>Тур ".$t."</h3>";
    echo "<table>";
    foreach ($q_game as $value){
        if ($value['round'] == $t){
            echo "<tr>";
                echo "<td class='tour-td'>".$value['t1']."</td>";
                if (!$value['s1']&&!$value['s2']){
                    //если нет результатов запись
                    echo "<td colspan='2' align='center' width='61px'>";
                        edit("<img width='15px' src='http://".$_SERVER['HTTP_HOST']."/img/edit.png'>");
                    echo "</td>";
                }else{
                    //Очки первой команды
                    if($value['s1']>$value['s2']){
                        echo "<td align='center' class='score-td -color'>";
                            edit($value['s1']);
                        echo "</td>";
                    }else{
                        echo "<td align='center' class='score-td'>";
                            edit($value['s1']);
                        echo "</td>";
                    }
                    //Очки второй команды
                    if($value['s2']>$value['s1']){
                        echo "<td align='center' class='score-td -color'>";
                            edit($value['s2']);
                        echo "</td>";
                    }else{
                        echo "<td align='center' class='score-td'>";
                            edit($value['s2']);
                        echo "</td>";
                    }
                }//end if
                echo "<td align='right' class='tour-td'>".$value['t2']."</td>";
            echo "</tr>";
        }//end if
    }//end foreach
    echo "</table>";
}//end for
echo "</div'>";

}else{
    echo "Доступ запрещен!";
}
echo "</div'>";