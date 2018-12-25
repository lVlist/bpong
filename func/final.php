<?php
require('../conf/dbconfig.php');
$id_game = $_GET['id'];

if($_POST['limit_val1']){

    /* Определяем порядок команд */
    if($_GET['limit'] == 8){
        $final = [1,5,7,3,4,8,6,2];
    }elseif($_GET['limit'] == 16){
        $final = [1,9,13,5,7,15,11,3,4,12,16,8,6,14,10,2];
    }elseif($_GET['limit'] == 32){
        $final = [1,17,25,9,13,29,21,5,7,23,31,15,11,27,19,3,4,20,28,12,16,32,24,8,6,22,30,14,10,26,18,2];
    }

    /* Получаем массив команд */
    foreach($_POST as $key=>$value){
        $team[] = $value; 
    } 

    /* Объеденяем массивы $final и $team в один массив где $final ключ а $team значение */
    $teams = array_combine($final,$team);
    ksort($teams);

    /* Записываем первый раунд с командами */
    $stmt = $conn->prepare("INSERT INTO final (id_game, id_t1, id_t2, round, block, next_block) VALUES (?,?,?,?,?,?)"); 
    $stmt->bind_param('iiiiii',$id_game, $t1, $t2, $round, $block,$j);

    /* Раунд 1 */
    
    $i=0;$j=0;foreach ($teams as $key => $value)
    {
        $i++;
        $round = 1;
        if($key%2 == 1)  //Проверяем четный ключ или нет
        {
            $t1 = $value;  //команда 1
        }else{
            $t2 = $value;  //команда 2
            $block = $i/2;
            if($block%2 == 1){
                $j++;
            }
            $stmt->execute();
        }
        
    }

    /* Записываем последующиие раунды без командам */
    $stmt = $conn->prepare("INSERT INTO final (id_game, round, block,next_block) VALUES (?,?,?,?)"); 
    $stmt->bind_param('iiii',$id_game, $round, $block,$j);

    /* Раунд 2 */ 
    if($_GET['limit']==8||$_GET['limit']==16||$_GET['limit']==32){
        $col = $block/2;
        $j = 0;
        for($i=1;$i<=$col;$i++){
            $round = 2;
            $block = $i;
            if($i%2 == 1){
                $j++;
            }
            $stmt->execute();
        }
    }
    /* Раунд 3 */ 
    if($_GET['limit']==16||$_GET['limit']==32){
        $col = $col/2;
        $j = 0;
        for($i=1;$i<=$col;$i++){
            $round = 3;
            $block = $i;
            if($i%2 == 1){
                $j++;
            }
            $stmt->execute();
        }    
    }

    /* Раунд 4 */ 
    if($_GET['limit']==32){
        $col = $col/2;
        $j = 0;
        for($i=1;$i<=$col;$i++){
            $round = 4;
            $block = $i;
            if($i%2 == 1){
                $j++;
            }
            $stmt->execute();
        } 
    }

    /* За 3 место */
        $round = $round+1;
        $block = 1;
        $stmt->execute();

    /* Финал */
        $round = $round+1;
        $block = 1;
        $stmt->execute();
header('Location: ../admin/final.php?id='.$id_game);
}    


/* Записываем резульаты игры */
$s1 = (int)$_POST['s1'];
$s2 = (int)$_POST['s2'];
$id_match = (int)$_POST['id_match'];
$id_game = (int)$_POST['id_game'];
$id_t1 = (int)$_POST['id_t1'];
$id_t2 = (int)$_POST['id_t2'];
$block = (int)$_POST['block'];
$next_block = (int)$_POST['next_block'];
$round = (int)$_POST['round'];
$next_round = $round+1;

if($_POST['id_match'])
{
    /* Записываем счет за игру */
    $stmt = $conn->prepare("UPDATE `final` SET `s1`=?, `s2`=? WHERE (`id`=?)"); 
    $stmt->bind_param('iii',$s1, $s2, $id_match);
    $stmt->execute();
    
    /* Получаем id команд играющих в след раунде */
    $final = $conn->query("SELECT final.id_t1, final.id_t2, final.id, final.block FROM final 
    WHERE final.id_game = $id_game AND final.round = $next_round AND final.block = $next_block");
    $final_team = $final->fetch_assoc();

    /* Получаем id команд играющих за 3-е место */
    $mesto3 = $next_round +1;
    $final2 = $conn->query("SELECT final.id_t1, final.id_t2, final.id, final.block FROM final 
    WHERE final.id_game = $id_game AND final.round = $mesto3 AND final.block = $next_block");
    $final_team2 = $final2->fetch_assoc();

    /* Получаем id последнего раунда */
    $id_last_round = $conn -> query ("SELECT final.round FROM final ORDER BY final.round DESC LIMIT 1");
    $last_round = $id_last_round->fetch_assoc();

    /* Записываем команду в следующий раунд */
    $stmt = $conn->prepare("UPDATE `final` SET `id_t1`=?, `id_t2`=? WHERE (`round`=?) AND (`block`=?)"); 
    $stmt->bind_param('iiii',$t1, $t2, $next_round, $next_block);

    /* Определяем и записываем выйгревшею команду в следующий раунд*/
    if($block%2 == 1){          //команда из нечетного блока
        if($s1>$s2){
            $t1 = $id_t1;
            $t2 = $final_team['id_t2'];
        }else{          
            $t1 = $id_t2;
            $t2 = $final_team['id_t2'];
        }
        if($round != $last_round['round']-1) //незаписываем если финальная игра
        {
            $stmt->execute(); 
        }
    }else{                      //команда из четного блока
        if($s1>$s2){
            $t2 = $id_t1;
            $t1 = $final_team['id_t1'];
        }else{
            $t2 = $id_t2;
            $t1 = $final_team['id_t1'];
        }
        if($round != $last_round['round']-1) //незаписываем если финальная игра
        {
            $stmt->execute();
        }
    }

    $next_round = $next_round+1; //в какой раунд записываем
    if($round == $last_round['round']-2){  //полуфинал
        if($block%2 == 1){
            if($s1<$s2){
                $t1 = $id_t1;
                $t2 = $final_team2['id_t2'];
            }else{
                $t1 = $id_t2;
                $t2 = $final_team2['id_t2'];
            }
        $stmt->execute();
        }else{
            if($s1<$s2){
                $t2 = $id_t1;
                $t1 = $final_team2['id_t1'];
            }else{
                $t2 = $id_t2;
                $t1 = $final_team2['id_t1'];
            }
        }
        $stmt->execute();
    }

    header('Location: ../admin/final.php?id='.$id_game);
}