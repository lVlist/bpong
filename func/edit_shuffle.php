<?php
require('../conf/dbconfig.php');
$id_game = $_POST['edit_game'];

/* Получаем массив из общей таблицы */
$teams = $conn->query("SELECT id_team FROM $dbt_qualification WHERE id_game = $id_game");
foreach($teams as $value){
    $teams_id[] .= $value['id_team'];
}

/* Получаем массив из первого тура */
$round1 = $conn->query("SELECT id_t1 as id_team FROM $dbt_q_games WHERE id_game = $id_game AND round = 1
                        UNION ALL 
                        SELECT id_t2 as id_team FROM $dbt_q_games WHERE id_game = $id_game AND round = 1");
foreach($round1 as $value){
    $round1_id[] .= $value['id_team'];
}

/* Проверяем четное количество команд или нет */
if(count($teams_id)%2 == 1){
    header('Location: ../admin/create.php?id='.$id_game.'&mes=even');
    exit;
}

/* Получаем массив новых команд */
$new_id = array_diff($teams_id, $round1_id);
$count = count($new_id)/2;

/* Проверяем появились новые команду или нет. Если нету сразу в турнир */
if(empty($new_id)){
    header('Location: ../admin/qualification.php?id='.$id_game);
    exit;
}

/* Удаляем 2,3 тур */
$conn->query("DELETE FROM `$dbt_q_games` WHERE (`id_game` = $id_game AND `round` IN(2,3))");

/* Записываем новые команды в конец 1 тура */
$stmt = $conn->prepare("INSERT INTO $dbt_q_games (id_game, id_t1, id_t2, round) VALUES (?,?,?,?)"); 
$stmt->bind_param('iiii',$id_game, $t1, $t2, $round);
$round_team = array_chunk($new_id, $count);

for($c=0;$c<=$count-1;$c++){
    $t1 = $round_team[0][$c];
    $t2 = $round_team[1][$c];
    $round = 1;
    $stmt->execute(); //запись в базу
}

$round1_new = $conn->query("SELECT id_t1, id_t2 FROM $dbt_q_games WHERE id_game = $id_game AND round = 1");
foreach($round1_new as $value){
    $array_team[0][] .= $value['id_t1'];
    $array_team[1][] .= $value['id_t2'];
}

/* Рандомим и записываем 2,3 тур */
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
$quantity_round = 2; //количество раундов

for ($i=1;$i<=$quantity_round;$i++){
    $round = $round+1;
        do{
            shuffle($teams_id);
            $round_team = array_chunk($teams_id, $count);
        }while(checkTeam($round_team, $array_team) === false);

        for($c=0;$c<=$count-1;$c++){
            $t1 = $round_team[0][$c];
            $t2 = $round_team[1][$c];
            $stmt->execute(); //запись в базу
        }
    
    /* Записываем прошлый тур в массив */
    for($c=0;$c<=$count-1;$c++){
        $array_team[0][] .= $round_team[0][$c];
        $array_team[1][] .= $round_team[1][$c];
    }
}
header('Location: ../admin/qualification.php?id='.$id_game);
exit;
?>