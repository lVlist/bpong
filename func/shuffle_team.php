<?php
require('../conf/dbconfig.php');
require('../conf/config.php');
require_once('func.php');

$id_game = $_POST['start_game'];

// получаем статус о telegram
$telegram = (int)$conn->query("SELECT telegram FROM $dbt_games WHERE id = $id_game")->fetch_object()->telegram;

/* Получаем массив команд для рандома */
$teams = $conn->query("SELECT id_team FROM $dbt_qualification WHERE id_game = $id_game");
foreach($teams as $value){
    $teams_id[] .= $value['id_team']; //массив команд для рандома
}

/* Проверяем четное количество команд или нет */
if(count($teams_id)%2 == 1){
    header('Location: ../admin/create.php?id='.$id_game.'&mes=even');
    die;
}

/* затираем результаты при редактировании турнира */
$conn->query("UPDATE `$dbt_qualification` SET `r1`=NULL, `r2`=NULL, `r3`=NULL, `result`=NULL, `d1`=NULL, `d2`=NULL, `d3`=NULL, `difference`=NULL WHERE (`id_game`= $id_game)");

/* Удаляем 3 тура при редактировании */
$conn->query("DELETE FROM `$dbt_q_games` WHERE (`id_game` = $id_game)");

/* Рандомим и записываем 3 тура */
$stmt = $conn->prepare("INSERT INTO $dbt_q_games (id_game, id_t1, id_t2, round) VALUES (?,?,?,?)"); 
$stmt->bind_param('iiii',$id_game, $t1, $t2, $round);

$count = count($teams_id)/2;

/* Проверяем играла ли команда в другом туре */
function checkTeam($round_current, $round_old){
    global $count;
    for($i=0;$i<=$count-1;$i++){
        $count_old = count($round_old[0]);
        for($j=0;$j<=$count_old-1;$j++){
            if($round_current[0][$i] == $round_old[0][$j] && $round_current[1][$i] == $round_old[1][$j]){
                $bool = false;
                break;
            }elseif($round_current[0][$i] == $round_old[1][$j] && $round_current[1][$i] == $round_old[0][$j]){
                $bool = false;
                break;
            }else{
                $bool = true;
            }
        }
        if ($bool === false) break;
    }
    return $bool;
}

/* Рандомим команды */
$array_team = NULL;
$quantity_round = 3; //количество раундов

for ($i=1;$i<=$quantity_round;$i++){
    if($array_team === NULL){
        shuffle($teams_id);
        $round_team = array_chunk($teams_id, $count);
        for($c=0;$c<=$count-1;$c++){
            $t1 = $round_team[0][$c];
            $t2 = $round_team[1][$c];
            $round = $i;
            $stmt->execute(); //запись в базу

        }
    }else{
        do{
            shuffle($teams_id);
            $round_team = array_chunk($teams_id, $count);
        }while(checkTeam($round_team, $array_team) === false);

        for($c=0;$c<=$count-1;$c++){
            $t1 = $round_team[0][$c];
            $t2 = $round_team[1][$c];
            $round = $i;
            $stmt->execute(); //запись в базу
        }
    }
    
    /* Записываем прошлый тур в массив */
    for($c=0;$c<=$count-1;$c++){
        $array_team[0][] .= $round_team[0][$c];
        $array_team[1][] .= $round_team[1][$c];
    }
}

//Telegram
if($telegram === 1){

    //Инфа что начался новый туринр
    $game = $conn->query("SELECT game FROM $dbt_games WHERE id = $id_game")->fetch_object()->game;

    $message = "‼ <b>Начался турнир - ".$game."</b> ‼";
    sendMessage($token, $chatID, $message);

//Тур 1 кто с кем играет
    $commands = $conn->query("SELECT t1.team as t1, t2.team as t2
FROM $dbt_q_games AS Q
INNER JOIN $dbt_teams t1 ON t1.id = Q.id_t1
INNER JOIN $dbt_teams t2 ON t2.id = Q.id_t2
WHERE id_game = $id_game AND Q.round = 1");

    $message = "<b>❗" . $game . " - игры 1 тура:❗</b>\n";
    foreach ($commands as $value){
        $message .= "🍻 " . $value['t1'] ." ⚔ " . $value['t2'] . "\n";
    }
    sendMessage($token, $chatID, $message);
}

header('Location: ../admin/qualification.php?id='.$id_game);
exit;