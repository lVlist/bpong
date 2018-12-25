<!DOCTYPE HTML>
<meta http-equiv="refresh" content="60">
<head>
<title>Beer Pong Minks - Бир Понг Минск - Аренда Beer Pong - Турниры по Beer Pong</title>
  <link href='css/style.css' rel='stylesheet'>
  <meta charset='utf-8'>
</head>
<?php
require('inc/dbconfig.inc.php');
$id_last_game = $conn -> query ("SELECT games.id FROM games ORDER BY games.id DESC LIMIT 1");
$last_game = $id_last_game->fetch_assoc();
		
//получения id игпы методом GET
        if (isset($_GET['game'])){
		$id_game = (int)$_GET['game'];
        }else{
		$id_game = (int)$last_game['id'];
        }


/* Выборка 3 туров */
$q_game = $conn->query("SELECT t1.team t1, q_games.s1, q_games.s2, t2.team t2, q_games.round,t1.id AS id_t1, t2.id AS id_t2, q_games.id AS id_match FROM q_games
INNER JOIN qualification AS id_t1 ON id_t1.key_team = q_games.key_team1
INNER JOIN qualification AS id_t2 ON id_t2.key_team = q_games.key_team2
INNER JOIN teams t1 ON t1.id = id_t1.id_team
INNER JOIN teams t2 ON t2.id = id_t2.id_team
WHERE id_t1.id_game = $id_game AND id_t2.id_game = $id_game AND q_games.id_game = $id_game");

/* Общая таблица 3 туров */
$qualification = $conn->query("SELECT teams.team, qualification.r1, qualification.r2, qualification.r3, qualification.result, qualification.difference, games.game FROM qualification
INNER JOIN teams ON teams.id = qualification.id_team
INNER JOIN games ON games.id = qualification.id_game
WHERE qualification.id_game = $id_game
ORDER BY qualification.result DESC, qualification.difference DESC");

function roundGame($round)
    {
        global $q_game, $value, $match;

        echo "<h3>Тур ".$round."</h3>";
        echo "<table>";
        foreach ($q_game as $value)
        {
            if ($value['round'] == $round)
            {
                echo "<tr>";
                    echo "<td width= '250px'>".$value['t1']."</td>";                    
                        if($value['s1']>$value['s2']){
                            echo "<td align='center' width='20px' height='23px' style='background-color: #2E8B57'>".$value['s2']."</td>";
                        }else{
                            echo "<td align='center' width='20px' height='23px'>".$value['s2']."</td>";
                        }
                        if($value['s2']>$value['s1']){
                            echo "<td align='center' width='20px' height='23px' style='background-color: #2E8B57'>".$value['s2']."</td>";
                        }else{
                            echo "<td align='center' width='20px' height='23px'>".$value['s2']."</td>";
                        }                    
                    echo "<td align='right' width= '250px'>".$value['t2']."</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
    }

    function winLoseView($value)
{
    if ($value == 3){echo "<td style='background-color: #2E8B57'>W</td>";}
    elseif ($value == 2){echo "<td style='background-color: #6bf'>OW</td>";}
    elseif ($value == 1){echo "<td style='background-color: #999'>OL</td>";}
    elseif (is_null($value)){echo "<td></td>";}
    else  {echo "<td style='background-color: #f66'>L</td>";}
}

/* Получаем название турнира */
$game = $qualification->fetch_assoc();
$game = $game['game'];

/* Общая таблица 3 туров */
echo "<div id='block1'>";
echo "<table>";
echo "<h3>Турнир: ".$game."</h3>";
echo "<tr align = 'center'>";
        echo "<td>№</td>";
        echo "<td>Команда</td>";
        echo "<td>Тур 1</td>";
        echo "<td>Тур 2</td>";
        echo "<td>Тур 3</td>";
        echo "<td>Итого</td>";
        echo "<td> Разница очков</td>";
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
echo "</div>";



/* Выводим 3 тура */
echo "<div id='block2'>";
roundGame(1);
roundGame(2);
roundGame(3);
echo "</div'>";