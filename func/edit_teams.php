<?php
require('../conf/dbconfig.php');

/* Редактирование команды */
if($_POST['edit_team']){
    $team = $_POST['team'];
    $team = $conn->real_escape_string($team);
    $id_team = $_POST['id_team'];
    $conn->query("UPDATE `$dbt_teams` SET `team`= '$team' WHERE (`id`= $id_team)");
    header('Location: ../admin/teams.php');
}

/* Удаление команды */
if($_POST['del_team']){
    $id_team = $_POST['id_team'];
    $conn->query("DELETE FROM `$dbt_teams` WHERE (`id` = $id_team)");
    header('Location: ../admin/teams.php');
}