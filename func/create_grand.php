<?php

require('../conf/dbconfig.php');
session_start();

$id_game = (int)$_POST['id_game'];
$count_teams = count($_SESSION['grand']);

/**
 * @param $r - номер раунда
 * @param $b - пколичество блоков в раунде
 * Генерирует новый раунд с уловием что команда попадет на 2 позицию след блока
 */

function newRoundBlockPosition2 ($r, $b){
    global $stmt, $round, $block, $nb, $bp;
    $nb = 0;
    $round = $r;

    for($i = 1; $i <= $b; $i++)
    {
        $block = $i;
        $nb = $i;
        $bp = 2;

        $stmt->execute();
    }
}


/**
 * @param $r - номер раунда
 * @param $b - пколичество блоков в раунде
 * Генерирует новый раунд с уловием что команды попадет на 1 и 2 позицию каждого блока
 */

function newRoundBlockPosition12 ($r, $b){
    global $stmt, $round, $block, $nb, $bp;
    $nb = 0;
    $round = $r;

    for($i=1;$i <= $b;$i++)
    {
        $block = $i;

        if($block%2 == 1){
            $nb++;
            $bp = 1;
        }else{
            $bp = 2;
        }

        $stmt->execute();
    }
}

/**
 * Записываем первый раунд с командами
 */
$stmt = $conn->prepare("INSERT INTO $dbt_grand (id_game, position, id_t1, id_t2, round, block, next_block, next_block_position) VALUES (?,?,?,?,?,?,?,?)"); 
$stmt->bind_param('isiiiiii',$id_game, $pos, $t1, $t2, $round, $block, $nb, $bp);


$pos = 'up';

if ($count_teams === 24){

    $j = 9;
    $nb = 0;
    for($i = 1; $i <= 8; $i++) {
        //записываем первые 8 мест во 2 раунд
        $t1 = $_SESSION['grand'][$i];
        $t2 = NULL;
        $round = 2;
        $block = $i;

        if ($block % 2 == 1) {
            $nb++;
            $bp = 1;
        } else {
            $bp = 2;
        }
        $stmt->execute();
    }

        for($i = 1; $i <= 8; $i++){
        //записываем с 9-24 места в 1 раунд
        $t1 = $_SESSION['grand'][$j];
        $j++;
        $t2 = $_SESSION['grand'][$j];
        $j++;
        $round = 1;
        $block = $i;
        $nb= $i;
        $bp = 2;

        $stmt->execute();
    }

}elseif ($count_teams === 32){

    $j = 1;
    for($i=1;$i <= count($_SESSION['grand'])/2;$i++)
    {
        $t1 = $_SESSION['grand'][$j];
        $j++;
        $t2 = $_SESSION['grand'][$j];
        $j++;
        $round = 1;
        $block = $i;

        if($block % 2 == 1){
            $nb++;
            $bp = 1;
        }else{
            $bp = 2;
        }

        $stmt->execute();
    }
}


/**
 * Записываем следующие раунды без команд верхней сетки
 */

$stmt = $conn->prepare("INSERT INTO $dbt_grand (id_game, position, round, block, next_block, next_block_position) VALUES (?,?,?,?,?,?)"); 
$stmt->bind_param('isiiii',$id_game, $pos, $round, $block, $nb, $bp);

if($count_teams == 24) {

    /* Раунд 3 */
    newRoundBlockPosition12(3, 4);

    /* Раунд 4 */
    newRoundBlockPosition12(4, 2);

    /* Финал */
    $round = 4;
    for($i=1;$i <= 2;$i++){
        $round = $round+1;
        $block = 1;
        $bp = 1;
        $stmt->execute();
    }

}elseif ($count_teams == 32) {

    /* Раунд 2 */
    newRoundBlockPosition12(2, 8);

    /* Раунд 3 */
    newRoundBlockPosition12(3, 4);

    /* Раунд 4 */
    newRoundBlockPosition12(4, 2);

    /* Финал */
    $round = 4;
    for($i=1;$i <= 2;$i++){
        $round = $round+1;
        $block = 1;
        $bp = 1;
        $stmt->execute();
    }
}


/**
 * Записываем следующие раунды без команд нижней сетки
 */

$stmt = $conn->prepare("INSERT INTO $dbt_grand (id_game, position, round, block, next_block, next_block_position) VALUES (?,?,?,?,?,?)"); 
$stmt->bind_param('isiiii',$id_game, $pos, $round, $block, $nb, $bp);

$pos = 'down';

if($count_teams == 24) {

    /* Раунд 1 */
    newRoundBlockPosition12(1, 8);

    /* Раунд 2 */
    newRoundBlockPosition2(2, 4);

    /* Раунд 3 */
    newRoundBlockPosition12(3, 4);

    /* Раунд 4 */
    newRoundBlockPosition2(4, 2);

    /* Раунд 5 */
    newRoundBlockPosition12(5, 2);

    /* Раунд 6 */
    newRoundBlockPosition2(6, 1);

    /* Финал */
    newRoundBlockPosition2(7, 1);

}elseif ($count_teams == 32) {

    /* Раунд 1 */
    newRoundBlockPosition2(1, 8);

    /* Раунд 2 */
    newRoundBlockPosition12(2, 8);

    /* Раунд 3 */
    newRoundBlockPosition2(3, 4);

    /* Раунд 4 */
    newRoundBlockPosition12(4, 4);

    /* Раунд 5 */
    newRoundBlockPosition2(5, 2);

    /* Раунд 6 */
    newRoundBlockPosition12(6, 2);

    /* Раунд 7 */
    newRoundBlockPosition2(7, 1);

    /* Финал */
    newRoundBlockPosition2(8, 1);
}

//Записываем команды статистику
for($i=1;$i <= $count_teams;$i++){
    $id_team = $_SESSION['grand'][$i];
    $conn->query("INSERT INTO $dbt_statistics (id_game, id_team) VALUES ($id_game, $id_team)");
}

session_destroy();

header('Location: ../admin/grand.php?id='.$id_game);
exit;