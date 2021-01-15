<?php
require('../conf/dbconfig.php');

$id_game = (int)$_GET['id'];
$limit = (int)$_GET['limit'];

$game_bronze = $conn->query("SELECT bronze FROM $dbt_games WHERE id = $id_game");
$bronze = $game_bronze->fetch_assoc();

if($_POST['limit_val1'] && count($_POST) === $limit) {

    $conn->query("DELETE FROM `$dbt_final` WHERE (`id_game` = $id_game)");
    $conn->query("DELETE FROM `$dbt_final_score` WHERE (`id_game` = $id_game)");

    /* Получаем массив команд */
    foreach($_POST as $key=>$value){
        $team[] = $value;
    }

    //перемешиваем массив команд
    shuffle($team);

    /* Записываем первый раунд с командами */
    $stmt = $conn->prepare("INSERT INTO $dbt_final (id_game, id_t1, id_t2, round, block, next_block, next_block_position) VALUES (?,?,?,?,?,?,?)");
    $stmt->bind_param('iiiiiii',$id_game, $t1, $t2, $round, $block, $nb, $bp);



    //получаем массивы 1 - next_block, 2 - next_block_position и рандомим их
    function randomNextBlock ($count){
        $j = 1;
        for ($i = 1; $i <= $count; $i++){

            $arrNb[1][] = $j;

            if($i%2 == 1) {
                $arrNb[2][] = 1;
            }else{
                $arrNb[2][] = 2;
                $j++;
            }
        }

        $keys = array_keys($arrNb[1]);

        shuffle($keys);

        $i = 1;
        foreach($keys as $key) {
            $new[$i] = $arrNb[1][$key];
            $i++;
        }

        $arrNb[1] = $new;

        $j = 1;
        foreach($keys as $key) {
            $new2[$j] = $arrNb[2][$key];
            $j++;
        }

        $arrNb[2] = $new2;

        return $arrNb;
    }


    $randomNextBlock = randomNextBlock($limit/2);

    // Раунд 1 - запись в базу
    $i=1;
    foreach ($team as $key => $value)
    {
        $round = 1;

        if($key%2 != 1)  //Проверяем четный ключ или нет
        {
            $t1 = $value;  //команда 1
        }else{
            $t2 = $value;  //команда 2
            $block = $i;

            $nb = $randomNextBlock[1][$i];
            $bp = $randomNextBlock[2][$i];

            $stmt->execute();
            $i++;
        }
    }

    /* Записываем последующие раунды без команд */
    $stmt = $conn->prepare("INSERT INTO $dbt_final (id_game, round, block, next_block, next_block_position) VALUES (?,?,?,?,?)");
    $stmt->bind_param('iiiii',$id_game, $round, $block, $nb, $bp);

    /* Раунд 2 */
    $arrRound = [
      '8' => '2',
      '16' => '3',
      '32' => '4',
      '64' => '5',
    ];

    $countBlock = $limit / 4;

    for ($i = 1; $i <= $arrRound[$limit];$i++){
        $randomNextBlock = randomNextBlock($countBlock);
        $round += 1;

        for ($j = 1; $j <= $countBlock;$j++){

            $block = $j;
            $nb = $randomNextBlock[1][$j];
            $bp = $randomNextBlock[2][$j];

            $stmt->execute();
        }
        $countBlock /= 2;
    }

    /* За 3 место */
    if((int)$bronze['bronze'] === 1){
        $round += 1;
        $block = 1;
        $stmt->execute();
    }

    header('Location: ../admin/final.php?id='.$id_game);
    exit;
}else{
    header('Location: ../admin/limit.php?id='.$id_game.'&limit='.$_GET['limit'].'&msg='.count($_POST));
    exit;
}