<?php
require('../conf/dbconfig.php');
$id_game = (int)$_POST['id_game'];
session_start();

/* Записываем первый раунд с командами */
$stmt = $conn->prepare("INSERT INTO $dbt_grand (id_game, position, id_t1, id_t2, round, block, next_block, next_block_position) VALUES (?,?,?,?,?,?,?,?)"); 
$stmt->bind_param('isiiiiii',$id_game, $pos, $t1, $t2, $round, $block, $nb, $bp);

$j = 1;
$pos = 'up';
for($i=1;$i <= count($_SESSION['grand'])/2;$i++)
{
    $t1 = $_SESSION['grand'][$j];
    $j++;
    $t2 = $_SESSION['grand'][$j];
    $j++;
    $round = 1;
    $block = $i;
    if($block%2 == 1){
        $nb++;
        $bp = 1;
    }else{
        $bp = 2;
    }
    $stmt->execute();
}

/* Записываем первый раунд с командами */
$stmt = $conn->prepare("INSERT INTO $dbt_grand (id_game, position, round, block, next_block, next_block_position) VALUES (?,?,?,?,?,?)"); 
$stmt->bind_param('isiiii',$id_game, $pos, $round, $block, $nb, $bp);

$count_teams = count($_SESSION['grand']);

/* Раунд 2 */ 
if($count_teams==8||$count_teams==16||$count_teams==32){
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
if($count_teams==16||$count_teams==32){
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
if($count_teams==32){
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

/* Финал */
for($i=1;$i <= 2;$i++){
    $round = $round+1;
    $block = 1;
    $bp = 1;
    $stmt->execute();
}



/* Записываем первый раунд с командами */
$stmt = $conn->prepare("INSERT INTO $dbt_grand (id_game, position, round, block, next_block, next_block_position) VALUES (?,?,?,?,?,?)"); 
$stmt->bind_param('isiiii',$id_game, $pos, $round, $block, $nb, $bp);

$count_teams = count($_SESSION['grand']);
$pos = 'down';

/* Раунд 1-2 */ 
for($j=1;$j<=2;$j++){
    $nb = 0;
    for($i=1;$i <= count($_SESSION['grand'])/4;$i++)
    {
        $round = $j;
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

/* Раунд 3-4 */ 
if($count_teams==16||$count_teams==32){
    $col = $block/2;
    for($j=1;$j<=2;$j++){
        $nb = 0;
        $round = $round + 1;
        for($i=1;$i<=$col;$i++){
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
}

/* Раунд 5-6 */ 
if($count_teams==32){ 
    $col = $block/2;
    for($j=1;$j<=2;$j++){
        $nb = 0;
        $round = $round + 1;
        for($i=1;$i<=$col;$i++){
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
}


/* Финал */
for($i=1;$i <= 2;$i++){
    $round = $round+1;
    $block = 1;
    $bp = $i;
    $stmt->execute();
}


//Записываем её в статистику
for($i=1;$i <= count($_SESSION['grand']);$i++){
    $id_team = $_SESSION['grand'][$i];
    $conn->query("INSERT INTO $dbt_statistics (id_game, id_team) VALUES ($id_game, $id_team)");
}


session_destroy();

header('Location: ../admin/grand.php?id='.$id_game);
exit;