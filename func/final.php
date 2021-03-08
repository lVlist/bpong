<?php
/* Финал 8-16-32 */
require('../conf/dbconfig.php');
require('../conf/config.php');
require_once('func.php');
$id_game = (int)$_GET['id'];

// получаем статус о telegram
$telegram = (int)$conn->query("SELECT telegram FROM $dbt_games WHERE id = $id_game")->fetch_object()->telegram;

$game_bronze = $conn->query("SELECT bronze FROM $dbt_games WHERE id = $id_game");
$bronze = $game_bronze->fetch_assoc();

if($_POST['limit_val1'] && count($_POST) == $_GET['limit']){

    $conn->query("DELETE FROM `$dbt_final` WHERE (`id_game` = $id_game)");
    $conn->query("DELETE FROM `$dbt_final_score` WHERE (`id_game` = $id_game)");
    
    /**
     * Определяем порядок команд
     * TODO нужен алгоритм сортировки
     */
    if($_GET['limit'] == 8){
        $final = [1,5,7,3,4,8,6,2];
    }elseif($_GET['limit'] == 16){
        $final = [1,9,13,5,7,15,11,3,4,12,16,8,6,14,10,2];
    }elseif($_GET['limit'] == 32){
        $final = [1,17,25,9,13,29,21,5,7,23,31,15,11,27,19,3,4,20,28,12,16,32,24,8,6,22,30,14,10,26,18,2];
    }elseif($_GET['limit'] == 64){
        $final = [1,33,49,17,25,57,41,9,13,45,61,29,21,53,37,5,7,39,55,23,31,63,47,15,11,43,59,27,19,51,35,3,4,36,52,20,28,60,44,12,16,48,64,32,24,56,40,8,6,38,54,22,30,62,46,14,10,42,58,26,18,50,34,2];
    }

    /* Получаем массив команд */
    foreach($_POST as $key=>$value){
        $team[] = $value;

    }

    /* Объеденяем массивы $final и $team в один массив где $final ключ а $team значение */
    $teams = array_combine($final,$team);
    ksort($teams);
    

    /* Записываем первый раунд с командами */
    $stmt = $conn->prepare("INSERT INTO $dbt_final (id_game, id_t1, id_t2, round, block, next_block, next_block_position) VALUES (?,?,?,?,?,?,?)"); 
    $stmt->bind_param('iiiiiii',$id_game, $t1, $t2, $round, $block, $nb, $bp);

    /* Раунд 1 */
    $teleg_mes = "";
    $i=0;$nb=0;
    foreach ($teams as $key => $value)
    {
        $i++;
        $round = 1;
        
        if($key%2 == 1)  //Проверяем четный ключ или нет
        {
            $t1 = $value;  //команда 1
            $teleg_mes .= "🍻 " . $t1 ." ⚔ ";
        }
        else
        {
            $t2 = $value;  //команда 2
            $teleg_mes .= $t2. "\n";
            $block = $i/2;

            if($block%2 == 1){
                $nb++;
                $bp = 1;
            }else{
                $bp = 2;
            }
            
            $stmt->execute();
        }
        
    }
    

    /* Записываем последующие раунды без команд */
    $stmt = $conn->prepare("INSERT INTO $dbt_final (id_game, round, block, next_block, next_block_position) VALUES (?,?,?,?,?)"); 
    $stmt->bind_param('iiiii',$id_game, $round, $block, $nb, $bp);

    /* Раунд 2 */ 
    if($_GET['limit'] == 8 || $_GET['limit'] == 16 || $_GET['limit'] == 32 || $_GET['limit'] == 64){
        $col = $block/2;
        $nb = 0;
        for($i=1;$i<=$col;$i++){
            $round = 2;
            $block = $i;
            if($i%2 == 1){
                $nb++;
                $bp = 1;
            }else{
                $bp = 2;
            }
            $stmt->execute();
        }
    }
    /* Раунд 3 */ 
    if($_GET['limit'] == 16 || $_GET['limit'] == 32 || $_GET['limit'] == 64){
        $col = $col/2;
        $nb = 0;
        for($i=1;$i<=$col;$i++){
            $round = 3;
            $block = $i;
            if($i%2 == 1){
                $nb++;
                $bp = 1;
            }else{
                $bp = 2;
            }
            $stmt->execute();
        }    
    }

    /* Раунд 4 */ 
    if($_GET['limit'] == 32 || $_GET['limit'] == 64){
        $col = $col/2;
        $nb = 0;
        for($i=1;$i<=$col;$i++){
            $round = 4;
            $block = $i;
            if($i%2 == 1){
                $nb++;
                $bp = 1;
            }else{
                $bp = 2;
            }
            $stmt->execute();
        } 
    }

    /* Раунд 5 */
    if($_GET['limit'] == 64){
        $col = $col/2;
        $nb = 0;
        for($i=1;$i<=$col;$i++){
            $round = 5;
            $block = $i;
            if($i%2 == 1){
                $nb++;
                $bp = 1;
            }else{
                $bp = 2;
            }
            $stmt->execute();
        }
    }

    /* Финал */
        $round = $round+1;
        $block = 1;
        $stmt->execute();

    /* За 3 место */
    if((int)$bronze['bronze'] === 1){
        $round = $round+1;
        $block = 1;
        $stmt->execute();
    }

//Telegram
    if($telegram === 1) {

        /* Получаем id последнего раунда */
        $id_last_round = $conn->query("SELECT F.round FROM $dbt_final F
                        WHERE F.id_game = $id_game ORDER BY F.round DESC LIMIT 1");
        $last_round = $id_last_round->fetch_assoc();

        $arr_rounds = ["🏆 ФИНАЛ 🏆", "1/2 финала", "1/4 финала", "1/8 финала", "1/16 финала", "1/32 финала", "1/64 финала"];

        if ((int)$bronze['bronze'] === 1) {
            $arr_final = ["🥉 Финал - игра за 3-е место 🥉"];
            for ($i = 1; $i <= $last_round['round'] - 1; $i++) {
                array_unshift($arr_final, $arr_rounds[$i - 1]);
            }
        } else {
            $arr_final = [];
            for ($i = 1; $i <= $last_round['round']; $i++) {
                array_unshift($arr_final, $arr_rounds[$i - 1]);
            }
        }

        //Инфа что начался новый туринр
        $game = $conn->query("SELECT game FROM $dbt_games WHERE id = $id_game")->fetch_object()->game;

        $message = "‼ <b>" . $game . " - начался финал</b> ‼";
        sendMessage($token, $chatID, $message);

        //Тур 1 кто с кем играет
        $commands = $conn->query("SELECT t1.team as t1, t2.team as t2 FROM $dbt_final F
                                INNER JOIN $dbt_teams AS t1 ON t1.id = F.id_t1
                                INNER JOIN $dbt_teams AS t2 ON t2.id = F.id_t2
                                WHERE id_game = $id_game AND F.round = 1
                                ORDER BY block");

        $message = "<b>❗" . $game . " - игры " . $arr_final[0] . ":❗</b>\n";
        foreach ($commands as $value) {
            $message .= "🍻 " . $value['t1'] . " ⚔ " . $value['t2'] . "\n";
        }
        sendMessage($token, $chatID, $message);
    }
    header('Location: ../admin/final.php?id='.$id_game);
    exit;
}else{
    header('Location: ../admin/limit.php?id='.$id_game.'&limit='.$_GET['limit'].'&msg='.count($_POST));
    exit;
}