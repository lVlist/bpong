<?php
require('../conf/dbconfig.php');
$id_game = (int)$_POST['id_game'];
session_start();



/* Записываем первый раунд с командами */
$stmt = $conn->prepare("INSERT INTO $dbt_grand (id_game, position, id_t1, id_t2, round, block, next_block, next_block_position) VALUES (?,?,?,?,?,?,?,?)"); 
$stmt->bind_param('isiiiiii',$id_game, $pos, $t1, $t2, $round, $block, $nb, $bp);


$pos = 'up';

if (count($_SESSION['grand']) === 24){

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

}else{

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


/* Записываем следующие раунды без команд верхней сетки */
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
}elseif($count_teams==24){
    $nb = 0;
    for($i=1;$i<=4;$i++) {
        $round = 3;
        $block = $i;

        if ($i % 2 == 1) {
            $nb++;
            $bp = 1;
        } else {
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
}elseif ($count_teams==24){
    $nb = 0;
    for($i=1;$i<=2;$i++){
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



/* Записываем следующие раунды без команд нижней сетки */
$stmt = $conn->prepare("INSERT INTO $dbt_grand (id_game, position, round, block, next_block, next_block_position) VALUES (?,?,?,?,?,?)"); 
$stmt->bind_param('isiiii',$id_game, $pos, $round, $block, $nb, $bp);

$count_teams = count($_SESSION['grand']);
$pos = 'down';

if($count_teams == 24) {
    /* Раунд 1 */
        $nb = 0;
        for($i=1;$i <= 8;$i++)
        {
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

    /* Раунд 2 */
    $nb = 0;
    $round = 2;

    for($i=1;$i <= 4;$i++)
    {
        $block = $i;
        $nb = $i;
        $bp = 2;

        $stmt->execute();
    }

    /* Раунд 3 */
    $nb = 0;
    $round = 3;

    for($i=1;$i <= 4;$i++)
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

    /* Раунд 4 */
    $nb = 0;
    $round = 4;

    for($i=1;$i <= 2;$i++)
    {
        $block = $i;
        $nb = $i;
        $bp = 2;

        $stmt->execute();
    }

    /* Раунд 5 */
    $nb = 0;
    $round = 5;

    for($i=1;$i <= 2;$i++)
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

    /* Раунд 6 */
    for($i=1;$i <= 2;$i++) {
        $nb = 0;
        $round++;
        $block = 1;
        $nb = 1;
        $bp = 2;

        $stmt->execute();
    }


}else{
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
}




//Записываем её в статистику
for($i=1;$i <= count($_SESSION['grand']);$i++){
    $id_team = $_SESSION['grand'][$i];
    $conn->query("INSERT INTO $dbt_statistics (id_game, id_team) VALUES ($id_game, $id_team)");
}

session_destroy();

header('Location: ../admin/grand.php?id='.$id_game);
exit;