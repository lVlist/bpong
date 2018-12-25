<?php
require('../conf/dbconfig.php');

$id_game = $_POST['start_game'];
$teams = $conn->query("SELECT qualification.id_team FROM qualification WHERE qualification.id_game = $id_game");
$stmt = $conn->prepare("UPDATE `qualification` SET `key_team`=? WHERE (`id_team`=?) AND (`id_game`=?)");
$stmt->bind_param('iii',$key_team, $id_team, $id_game);
$i = 0;
foreach($teams as $value){
    $i++;
    $key_team = $i;
    $id_team = $value['id_team'];
    $stmt->execute();
    $teams_id[] .= $i; //массив ключей команд для их перемещивания
}

/* Удаляем 3 тура при редактировании */
$conn->query("DELETE FROM `q_games` WHERE (`id_game` = $id_game)");

/* Рандомим и записываем 3 тура */
$stmt = $conn->prepare("INSERT INTO q_games (id_game, key_team1, key_team2, round) VALUES (?,?,?,?)"); 
$stmt->bind_param('iiii',$id_game, $t1, $t2, $round);

/* Раунд 1 */
foreach ($teams_id as $key => $value)
{
    $round = 1;
    if($key%2 == 0)  //Проверяем четный ключ или нет
    {
        $t1 = $value;  //команда 1
    }else{
        $t2 = $value;  //команда 2
        $stmt->execute();
    }
}

/* Раунд 2 */
$count = count($teams_id)/2; //половиним количество команд
$half = array_chunk($teams_id, $count); //разбивает массив на части, размер каждой части($count)

for ($i=0;$i<$count;$i++)
{
    $round = 2;
        $t1 = $half[0][$i];  //команда 1
        $t2 = $half[1][$i];  //команда 2
    $stmt->execute();
}

/* Раунд 3 */
$revers1 = array_reverse($half[0]); //переворачиваю первую половину команд
$revers2 = array_reverse($half[1]); //переворачиваю вторую половину команд
$pop = array_shift($revers2);         //удаляет первый элемент массива и возвращает его
$revers2[] = $pop;      //записывает первый элемент массива в конец массива

for ($i=0;$i<$count;$i++)
{
    $round = 3;
        $t1 = $revers2[$i];  //команда 1
        $t2 = $revers1[$i];  //команда 2
    $stmt->execute();
}

header('Location: ../admin/qualification.php?id='.$id_game);