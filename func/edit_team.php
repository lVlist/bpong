<?php
require('../conf/dbconfig.php');

if($_POST['editTeam']){

    $id_team = $_POST['id_team'];
    $id_game = $_POST['id_game'];
    $edit_team = $_POST['edit_team'];

    //проверяем зарегана команда в турнире
    $team = $conn->query("SELECT qualification.id_team FROM qualification WHERE qualification.id_game = $id_game");
    
    foreach ($team as $value){
        if($value['id_team'] == $id_team){
            $error = true;
            break;
        }
    }

    if($error == true){
        header('Location: ../admin/create.php?id='.$id_game.'&mes=err');
    }else{
        $conn->query("UPDATE `q_games` SET `id_t1`= '$id_team' WHERE (`id_t1`= $edit_team) AND (`id_game`= $id_game)");
        $conn->query("UPDATE `q_games` SET `id_t2`= '$id_team' WHERE (`id_t2`= $edit_team) AND (`id_game`= $id_game)");
        $conn->query("UPDATE `qualification` SET `id_team`= '$id_team' WHERE (`id_team`= $edit_team) AND (`id_game`= $id_game)");
        $conn->query("UPDATE `statistics` SET `id_team`= '$id_team' WHERE (`id_team`= $edit_team) AND (`id_game`= $id_game)");
        header('Location: ../admin/qualification.php?id='.$id_game);
    }
}