<?php
require('../conf/dbconfig.php');


if($_POST['edit_hal9va']){
    $id_team = $_POST['id_team'];
    $id_game = $_POST['id_game'];
    $conn->query("UPDATE `q_games` SET `id_t1`= '$id_team' WHERE (`id_t1`= 31) AND (`id_game`= $id_game)");
    $conn->query("UPDATE `q_games` SET `id_t2`= '$id_team' WHERE (`id_t2`= 31) AND (`id_game`= $id_game)");
    $conn->query("UPDATE `qualification` SET `id_team`= '$id_team' WHERE (`id_team`= 31) AND (`id_game`= $id_game)");
    $conn->query("UPDATE `statistics` SET `id_team`= '$id_team' WHERE (`id_team`= 31) AND (`id_game`= $id_game)");
    header('Location: ../admin/qualification.php?id='.$id_game);
}