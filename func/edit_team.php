<?php
require('../conf/dbconfig.php');
//var_dump($_POST);die;

if($_POST['editTeam']){
    $id_team = $_POST['id_team'];
    $id_game = $_POST['id_game'];
    $edit_team = $_POST['edit_team'];
    $conn->query("UPDATE `q_games` SET `id_t1`= '$id_team' WHERE (`id_t1`= $edit_team) AND (`id_game`= $id_game)");
    $conn->query("UPDATE `q_games` SET `id_t2`= '$id_team' WHERE (`id_t2`= $edit_team) AND (`id_game`= $id_game)");
    $conn->query("UPDATE `qualification` SET `id_team`= '$id_team' WHERE (`id_team`= $edit_team) AND (`id_game`= $id_game)");
    $conn->query("UPDATE `statistics` SET `id_team`= '$id_team' WHERE (`id_team`= $edit_team) AND (`id_game`= $id_game)");
    header('Location: ../admin/qualification.php?id='.$id_game);
}