<?php
require('../conf/dbconfig.php');
$id_game = $_GET['id'];

if($_POST['limit_val1'] && count($_POST) == $_GET['limit']){

    $conn->query("DELETE FROM `final` WHERE (`id_game` = $id_game)");

    /* Определяем порядок команд */
    if($_GET['limit'] == 12){
        $lim = 4;
        $final1 = [3,7,5,1,2,6,8,4];
        $final2 = [1,3,4,2];
    }elseif($_GET['limit'] == 24){
        $lim = 8;
        $final1 = [3,11,15,7,5,13,9,1,2,10,14,6,8,16,12,4];
        $final2 = [1,5,7,3,4,8,6,2];
    }

    /* Получаем массив команд */
    $i=0;foreach($_POST as $key=>$value){
        $i++;if($i <= $lim){
            $team2[] = $value; 
        }else{
            $team1[] = $value; 
        }
    }
 
    /* Объеденяем массивы $final и $team в один массив где $final ключ а $team значение */
    //раунд 1
    $teams1 = array_combine($final1,$team1);
    ksort($teams1);

    //раунд 2
    $teams2 = array_combine($final2,$team2);
    ksort($teams2);

    /* Записываем первый раунд с командами */
    $stmt = $conn->prepare("INSERT INTO final (id_game, id_t1, id_t2, round, block, next_block) VALUES (?,?,?,?,?,?)"); 
    $stmt->bind_param('iiiiii',$id_game, $t1, $t2, $round, $block,$j);

    /* Раунд 1 */
    $i=0;$j=0;foreach ($teams1 as $key => $value)
    {
        $i++;
        $round = 1;
        if($key%2 == 1)  //Проверяем четный ключ или нет
        {
            $t1 = $value;  //команда 1
        }else{
            $t2 = $value;  //команда 2
            $block = $i/2*2;
            $j = $i/2;
            $stmt->execute();
        }
    }

    /* Раунд 2 */
    $i=0;$j=0;foreach ($teams2 as $key => $value)
    {
        $i++;
        $round = 2;
        $t1 = $value;  //команда 1
        $t2 = NULL;  //команда 2
        $block = $i;

        //определяем next_block
        if($block%2 == 1){
            $j++;
        }
        $stmt->execute();
    }

    /* Записываем последующие раунды без команд */
    $stmt = $conn->prepare("INSERT INTO final (id_game, round, block,next_block) VALUES (?,?,?,?)"); 
    $stmt->bind_param('iiii',$id_game, $round, $block,$j);

    /* Раунд 3 */ 
    if($_GET['limit']==12||$_GET['limit']==24){
        $col = $block/2;
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
    if($_GET['limit']==24){
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
    if($_GET['final'] == 1){
        $round = $round+1;
        $block = 1;
        $stmt->execute();
    }elseif($_GET['final'] == 3){
        $round = $round+1;
        for($i=1;$i<=3;$i++){
            $block = $i;
            $stmt->execute();
        }
    }

    header('Location: ../admin/final.php?id='.$id_game);
}else{
    header('Location: ../admin/limit.php?id='.$id_game.'&limit='.$_GET['limit'].'&msg='.count($_POST));
}