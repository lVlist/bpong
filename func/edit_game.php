<?php
require('../conf/dbconfig.php');

/* Записываем  название турнира */ 
if ($_POST['new_game'])
{
    $game = $conn->prepare("INSERT INTO games (game, date) VALUES (?,?)");
    $game->bind_param('ss',htmlspecialchars($_POST['new_game']), date("Y-m-d"));
    $game->execute();
    $id_game = $game->insert_id;
    header('Location: ../admin/create.php?id='.$id_game);
}

/* Обновление названия турнира */
if($_POST['upd_game'])
{
    $id_game = $_POST['upd_game'];
    $game = $_POST['game'];
    $conn->query("UPDATE `games` SET `game`= '$game' WHERE (`id`= $id_game)");
    header('Location: ../admin/create.php?id='.$id_game);
}

/* Создание команды и запись в турнир */
if($_POST['new_team'])
{
    //Создаем команду
    $team = $_POST['new_team'];
    $conn->query("INSERT INTO teams (team) VALUES ('$team')");
    //Добавляем ее в турнир
    $id_team = $conn->insert_id;
    $id_game = $_POST['id_game'];
    $conn->query("INSERT INTO qualification (id_game, id_team) VALUES ($id_game, $id_team)");
    header('Location: ../admin/create.php?id='.$id_game);
}

/* Добавление команды в турнир */
if($_POST['add_team'])
{
    $id_game = $_POST['id_game'];
    $id_team = $_POST['add_team'];
    $team = $conn->query("SELECT qualification.id_team FROM qualification WHERE qualification.id_game = $id_game");
    $error = "";
    foreach ($team as $value)
    {
        //echo $value['id_team']."-".$id_team."<br>";
        if($value['id_team'] == $id_team)
        {
            $error = true;
            break;
        }
    }
    //echo $error; die;
    if($error == true){
        header('Location: ../admin/create.php?id='.$id_game.'&mes=err');
    }else{
        $conn->query("INSERT INTO qualification (id_game, id_team) VALUES ($id_game, $id_team)");
        header('Location: ../admin/create.php?id='.$id_game);
    }
    
}

/* Удаление команды */
if($_POST['del_team'])
{
    $id_game = $_POST['id_game'];
    $id_team = $_POST['del_team'];
    $conn->query("DELETE FROM `qualification` WHERE (`id_game` = $id_game) AND (`id_team` = $id_team)");
    header('Location: ../admin/create.php?id='.$id_game);
}


if($_POST['del_game'])
{
    $id_game = $_POST['del_game'];
    $conn->query("DELETE FROM `qualification` WHERE (`id_game` = $id_game)");
    $conn->query("DELETE FROM `q_games` WHERE (`id_game` = $id_game)");
    $conn->query("DELETE FROM `final` WHERE (`id_game` = $id_game)");
    $conn->query("DELETE FROM `games` WHERE (`id` = $id_game)");
    header('Location: ../index.php');
}