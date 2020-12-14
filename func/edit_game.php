<?php

require '../conf/dbconfig.php';
require 'func.php';
session_start();

// Записываем  название турнира
if ($_POST['new_game']) {
    $new_game = $_POST['new_game'];
    $date = date('Y-m-d');
    $type = $_POST['type'];
    $bronze = (int) $_POST['bronze'];
    $game = $conn->prepare("INSERT INTO {$dbt_games} (`game`, `type`, `bronze`, `date`) VALUES (?,?,?,?)");
    $game->bind_param('ssis', $new_game, $type, $bronze, $date);
    $game->execute();
    $id_game = $game->insert_id;
    if ('grand' == $type) {
        header('Location: ../admin/create_grand.php?id='.$id_game.'&type=32');
        exit;
    }
    header('Location: ../admin/create.php?id='.$id_game);
    exit;
}

// Обновление названия турнира
if ($_POST['upd_game']) {
    $id_game = $_POST['upd_game'];
    $game = $_POST['game'];
    $conn->query("UPDATE `{$dbt_games}` SET `game`= '{$game}' WHERE (`id`= {$id_game})");
    header('Location: ../admin/create.php?id='.$id_game);
    exit;
}

// Создание команды и запись в турнир
if ($_POST['new_team']) {
    $id_game = $_POST['id_game'];

    //определяем тип команды
    $types = $conn->query("SELECT type FROM {$dbt_games} Q WHERE Q.id = {$id_game}")->fetch_assoc();
    if ('sat' == $types['type'] or 'thu' == $types['type']) {
        $type = 'main';
    } else {
        $type = $types['type'];
    }

    //проверка на одинаковые названия
    $teams = $conn->query("SELECT team FROM {$dbt_teams} WHERE type = '{$type}'");
    foreach ($teams as $value) {
        if (0 == mb_strcasecmp($_POST['new_team'], $value['team'])) {
            header('Location: ../admin/create.php?id='.$id_game.'&mes=team');
            exit;
        }
    }

    //Создаем команду
    $team = htmlspecialchars_decode($_POST['new_team'], ENT_HTML5);
    $team = $conn->real_escape_string($team);
    $date = date('Y-m-d');
    $conn->query("INSERT INTO {$dbt_teams} (date, team, type) VALUES ('{$date}','{$team}', '{$type}')");

    //Добавляем её в турнир
    $id_team = $conn->insert_id;
    $conn->query("INSERT INTO {$dbt_qualification} (id_game, id_team) VALUES ({$id_game}, {$id_team})");

    //Записываем её в статистику
    $conn->query("INSERT INTO {$dbt_statistics} (id_game, id_team) VALUES ({$id_game}, {$id_team})");

    header('Location: ../admin/create.php?id='.$id_game);
    exit;
}

// Добавление команды в турнир
if ($_POST['add_team']) {
    $id_game = $_POST['id_game'];
    $id_team = $_POST['add_team'];
    $team = $conn->query("SELECT Q.id_team FROM {$dbt_qualification} Q WHERE Q.id_game = {$id_game}");
    $error = '';
    foreach ($team as $value) {
        if ($value['id_team'] == $id_team) {
            $error = true;

            break;
        }
    }

    if (true == $error) {
        header('Location: ../admin/create.php?id='.$id_game.'&mes=err');
        exit;
    }
    $conn->query("INSERT INTO {$dbt_qualification} (id_game, id_team) VALUES ({$id_game}, {$id_team})");
    //Записываем её в статистику
    $conn->query("INSERT INTO {$dbt_statistics} (id_game, id_team) VALUES ({$id_game}, {$id_team})");
    header('Location: ../admin/create.php?id='.$id_game);
    exit;
}

// Замена команды в турнире
if ($_POST['changeTeam']) {
    $id_team = $_POST['id_team'];
    $id_game = $_POST['id_game'];
    $change_team = $_POST['change_team'];

    //проверяем зарегана команда в турнире
    $team = $conn->query("SELECT Q.id_team FROM {$dbt_qualification} Q WHERE Q.id_game = {$id_game}");

    foreach ($team as $value) {
        if ($value['id_team'] == $id_team) {
            $error = true;

            break;
        }
    }

    if (true == $error) {
        header('Location: ../admin/create.php?id='.$id_game.'&mes=err');
        exit;
    }
    $conn->query("UPDATE `{$dbt_q_games}` SET `id_t1`= '{$id_team}' WHERE (`id_t1`= {$change_team}) AND (`id_game`= {$id_game})");
    $conn->query("UPDATE `{$dbt_q_games}` SET `id_t2`= '{$id_team}' WHERE (`id_t2`= {$change_team}) AND (`id_game`= {$id_game})");
    $conn->query("UPDATE `{$dbt_qualification}` SET `id_team`= '{$id_team}' WHERE (`id_team`= {$change_team}) AND (`id_game`= {$id_game})");
    $conn->query("UPDATE `{$dbt_statistics}` SET `id_team`= '{$id_team}' WHERE (`id_team`= {$change_team}) AND (`id_game`= {$id_game})");
    header('Location: ../admin/create.php?id='.$id_game);
    exit;
}

// Удаление команды из турнира
if ($_POST['del_team']) {
    $id_game = $_POST['id_game'];
    $id_team = $_POST['del_team'];
    $check_team = $conn->query("SELECT r1 FROM {$dbt_qualification} WHERE id_game = {$id_game} AND id_team = {$id_team}")->fetch_assoc();

    if (null != $check_team['r1']) {
        header('Location: ../admin/create.php?id='.$id_game.'&mes=point');
        exit;
    }

    //удаление из таблицы
    $conn->query("DELETE FROM `{$dbt_qualification}` WHERE (`id_game` = {$id_game}) AND (`id_team` = {$id_team})");

    //удаление из статистики
    $conn->query("DELETE FROM `{$dbt_statistics}` WHERE (`id_game` = {$id_game}) AND (`id_team` = {$id_team})");

    //удаление из туров
    $conn->query("DELETE FROM `{$dbt_q_games}` WHERE (`id_game` = {$id_game}) AND (`id_t1` = {$id_team})");
    $conn->query("DELETE FROM `{$dbt_q_games}` WHERE (`id_game` = {$id_game}) AND (`id_t2` = {$id_team})");

    header('Location: ../admin/create.php?id='.$id_game);
    exit;
}

// Удаление игры
if ($_POST['del_game']) {
    $id_game = (int)$_POST['del_game'];
    $conn->query("DELETE FROM `{$dbt_qualification}` WHERE (`id_game` = {$id_game})");
    $conn->query("DELETE FROM `{$dbt_q_games}` WHERE (`id_game` = {$id_game})");
    $conn->query("DELETE FROM `{$dbt_final}` WHERE (`id_game` = {$id_game})");
    $conn->query("DELETE FROM `{$dbt_games}` WHERE (`id` = {$id_game})");
    header('Location: ../index.php');
    exit;
}

// Удаление гранд фианала
if ($_POST['del_grand']) {

    $id_game = (int)$_POST['del_grand'];
    $conn->query("DELETE FROM `{$dbt_grand}` WHERE (`id_game` = {$id_game})");
    $conn->query("DELETE FROM `{$dbt_grand_score}` WHERE (`id_game` = {$id_game})");
    $conn->query("DELETE FROM `{$dbt_games}` WHERE (`id` = {$id_game})");
    header('Location: ../admin/tournaments.php?year='.date('Y'));
    exit;
}

// Создание команды и запись в турнир
if ($_POST['new_team_grand']) {
    $id_game = $_POST['id_game'];
    $type_grand = $_POST['type'];
    $type = 'main';
    $pos = $_POST['pos'];

    //проверка на одинаковые названия
    $teams = $conn->query("SELECT team FROM {$dbt_teams} WHERE type = '{$type}'");
    foreach ($teams as $value) {
        if ($value['team'] == $_POST['new_team_grand']) {
            header('Location: ../admin/create_grand.php?id='.$id_game.'&mes='.$type_grand.'&mes=team');
            exit;
        }
    }

    //Создаем команду
    $team = htmlspecialchars_decode($_POST['new_team_grand'], ENT_HTML5);
    $team = $conn->real_escape_string($team);
    $conn->query("INSERT INTO {$dbt_teams} (team, type) VALUES ('{$team}', '{$type}')");

    //Добавляем её в турнир
    $id_team = $conn->insert_id;
    $_SESSION['grand'][$pos] = $id_team;

    header('Location: ../admin/create_grand.php?id='.$id_game.'&type='.$type_grand);
    exit;
}

// Добавление команды в турнир
if ($_POST['add_team_grand']) {
    $id_game = $_POST['id_game'];
    $type_grand = $_POST['type'];
    $id_team = $_POST['add_team_grand'];
    $pos = $_POST['pos'];

    $_SESSION['grand'][$pos] = $id_team;

    header('Location: ../admin/create_grand.php?id='.$id_game.'&type='.$type_grand);
    exit;
}
