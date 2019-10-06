<?php
require('../conf/dbconfig.php');

/* Записываем  название турнира */ 
if ($_POST['new_game'])
{
    
   $new_game = $_POST['new_game'];
    $date = date("Y-m-d");
    $type = $_POST['type'];
    $bronze = (int)$_POST['bronze'];
    $game = $conn->prepare("INSERT INTO $dbt_games (`game`, `type`, `bronze`, `date`) VALUES (?,?,?,?)");
    $game->bind_param('ssis', $new_game, $type, $bronze, $date);
    $game->execute();
    $id_game = $game->insert_id;
    header('Location: ../admin/create.php?id='.$id_game);
}

/* Обновление названия турнира */
if($_POST['upd_game'])
{
    $id_game = $_POST['upd_game'];
    $game = $_POST['game'];
    $conn->query("UPDATE `$dbt_games` SET `game`= '$game' WHERE (`id`= $id_game)");
    header('Location: ../admin/create.php?id='.$id_game);
}

/* Создание команды и запись в турнир */
if($_POST['new_team'])
{
    $id_game = $_POST['id_game'];

    //определяем тип команды
    $types = $conn->query("SELECT type FROM $dbt_games Q WHERE Q.id = $id_game")->fetch_assoc();
    if($types['type'] == 'sat' OR $types['type'] == 'thu'){
        $type = 'main';
    }else{
        $type = $types['type'];
    }

    //проверка на одинаковые названия
    $teams = $conn->query("SELECT team FROM $dbt_teams WHERE type = '$type'");
    foreach($teams as $value){
        if($value['team'] == $_POST['new_team']){
            header('Location: ../admin/create.php?id='.$id_game.'&mes=team');
            die;
        }
    }

    //Создаем команду
    $date = date('Y');
    $team = htmlspecialchars_decode($_POST['new_team'], ENT_HTML5);
    $team = $conn->real_escape_string($team);
    $conn->query("INSERT INTO $dbt_teams (team, type, date) VALUES ('$team', '$type', '$date')");

    //Добавляем её в турнир
    $id_team = $conn->insert_id;
    $conn->query("INSERT INTO $dbt_qualification (id_game, id_team) VALUES ($id_game, $id_team)");

    //Записываем её в статистику
    $conn->query("INSERT INTO $dbt_statistics (id_game, id_team) VALUES ($id_game, $id_team)");

    header('Location: ../admin/create.php?id='.$id_game);
}

/* Добавление команды в турнир */
if($_POST['add_team']){
    $id_game = $_POST['id_game'];
    $id_team = $_POST['add_team'];
    $team = $conn->query("SELECT Q.id_team FROM $dbt_qualification Q WHERE Q.id_game = $id_game");
    $error = "";
    foreach ($team as $value){
        if($value['id_team'] == $id_team){
            $error = true;
            break;
        }
    }
    if($error == true){
        header('Location: ../admin/create.php?id='.$id_game.'&mes=err');
    }else{
        $conn->query("INSERT INTO $dbt_qualification (id_game, id_team) VALUES ($id_game, $id_team)");
        header('Location: ../admin/create.php?id='.$id_game);
    }

    //Записываем её в статистику
    $conn->query("INSERT INTO $dbt_statistics (id_game, id_team) VALUES ($id_game, $id_team)");
}

/* Замена команды в турнире */
if($_POST['changeTeam']){

    $id_team = $_POST['id_team'];
    $id_game = $_POST['id_game'];
    $change_team = $_POST['change_team'];

    //проверяем зарегана команда в турнире
    $team = $conn->query("SELECT Q.id_team FROM $dbt_qualification Q WHERE Q.id_game = $id_game");
    
    foreach ($team as $value){
        if($value['id_team'] == $id_team){
            $error = true;
            break;
        }
    }

    if($error == true){
        header('Location: ../admin/create.php?id='.$id_game.'&mes=err');
    }else{
        $conn->query("UPDATE `$dbt_q_games` SET `id_t1`= '$id_team' WHERE (`id_t1`= $change_team) AND (`id_game`= $id_game)");
        $conn->query("UPDATE `$dbt_q_games` SET `id_t2`= '$id_team' WHERE (`id_t2`= $change_team) AND (`id_game`= $id_game)");
        $conn->query("UPDATE `$dbt_qualification` SET `id_team`= '$id_team' WHERE (`id_team`= $change_team) AND (`id_game`= $id_game)");
        $conn->query("UPDATE `$dbt_statistics` SET `id_team`= '$id_team' WHERE (`id_team`= $change_team) AND (`id_game`= $id_game)");
        header('Location: ../admin/create.php?id='.$id_game);
    }
}

/* Удаление команды из турнира */
if($_POST['del_team']){
    $id_game = $_POST['id_game'];
    $id_team = $_POST['del_team'];
    $check_team = $conn->query("SELECT r1 FROM $dbt_qualification WHERE id_game = $id_game AND id_team = $id_team")->fetch_assoc();

    if($check_team['r1'] != NULL){
        header('Location: ../admin/create.php?id='.$id_game.'&mes=point');
        die;
    }

    //удаление из таблицы
    $conn->query("DELETE FROM `$dbt_qualification` WHERE (`id_game` = $id_game) AND (`id_team` = $id_team)");

    //удаление из статистики
    $conn->query("DELETE FROM `$dbt_statistics` WHERE (`id_game` = $id_game) AND (`id_team` = $id_team)");

    //удаление из туров
    $conn->query("DELETE FROM `$dbt_q_games` WHERE (`id_game` = $id_game) AND (`id_t1` = $id_team)");
    $conn->query("DELETE FROM `$dbt_q_games` WHERE (`id_game` = $id_game) AND (`id_t2` = $id_team)");

    header('Location: ../admin/create.php?id='.$id_game);
}

/* Удаление игры */
if($_POST['del_game']){
    $id_game = $_POST['del_game'];
    $conn->query("DELETE FROM `$dbt_qualification` WHERE (`id_game` = $id_game)");
    $conn->query("DELETE FROM `$dbt_q_games` WHERE (`id_game` = $id_game)");
    $conn->query("DELETE FROM `$dbt_final` WHERE (`id_game` = $id_game)");
    $conn->query("DELETE FROM `$dbt_games` WHERE (`id` = $id_game)");
    header('Location: ../index.php');
}