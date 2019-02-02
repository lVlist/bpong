<?php
require('../conf/dbconfig.php');

$id_game = $_POST['start_game'];

/* затираем результаты при редактировании турнира */
$conn->query("UPDATE `qualification` SET `r1`=NULL, `r2`=NULL, `r3`=NULL, `result`=NULL, `d1`=NULL, `d2`=NULL, `d3`=NULL, `difference`=NULL WHERE (`id_game`= $id_game)");

/* Удаляем 3 тура при редактировании */
$conn->query("DELETE FROM `q_games` WHERE (`id_game` = $id_game)");

/* Чистим таблицу статистики финала */
$conn->query("DELETE FROM `statistics_final` WHERE (`id_game` = $id_game)");

/* Получаем массив команд для рандома */
$teams = $conn->query("SELECT id_team FROM qualification WHERE id_game = $id_game");
foreach($teams as $value){
    $teams_id[] .= $value['id_team']; //массив команд для рандома
}

/* Записываем команды в статистику финала */
$stmt = $conn->prepare("INSERT INTO statistics (id_game, id_team) VALUES (?,?)"); 
$stmt->bind_param('ii',$id_game, $id_team);
foreach($teams as $val){
    $id_team = $val['id_team'];
    $stmt->execute();
}


/* Рандомим и записываем 3 тура */
$stmt = $conn->prepare("INSERT INTO q_games (id_game, id_t1, id_t2, round) VALUES (?,?,?,?)"); 
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
header('Location: ../admin/qualification.php?id='.$id_game);