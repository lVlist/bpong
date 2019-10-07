<?php
require('../conf/dbconfig.php');

/* Редактирование команды */
if($_POST['edit_team']){
    $team = $_POST['team'];
    $team = $conn->real_escape_string($team);
    $id_team = $_POST['id_team'];
    $conn->query("UPDATE `$dbt_teams` SET `team`= '$team' WHERE (`id`= $id_team)");
    header('Location: ../admin/teams.php');
    exit;
}

/* Удаление команды */
if($_POST['del_team']){
    $id_team = $_POST['id_team'];

    //проверяем играла ли команда в турнире если да то не удаляем
    $check = $conn->query("SELECT id_game FROM $dbt_qualification WHERE id_team = $id_team")->fetch_assoc();

    if($check === NULL){
        $conn->query("DELETE FROM `$dbt_teams` WHERE (`id` = $id_team)");
    }else{
        header('Location: ../admin/teams.php?&mes=no_del');
        exit;
    }
    
    header('Location: ../admin/teams.php');
    exit;
}