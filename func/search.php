<?php
require('../conf/dbconfig.php');

/* Добавление команды в турнир */
if(!empty($_POST["add_team"])){

    $referal = "{$_POST['add_team']}%";
    $id_game = $_POST['id_game'];

    $types = $conn->query("SELECT type FROM games WHERE games.id = $id_game")->fetch_assoc();
    if($types['type'] == 'sat' OR $types['type'] == 'thu'){
        $type = 'main';
    }else{
        $type = $types['type'];
    }

    $stmt = $conn -> prepare("SELECT * from teams WHERE id != 1 AND date = year(curdate()) AND type = '$type' AND team LIKE (?)");
    $stmt->bind_param("s", $referal);
    $stmt->execute();

    $db_referal = $stmt->get_result();
    $row = $db_referal->num_rows;
    $team = htmlspecialchars($_POST["add_team"], ENT_QUOTES);
    
    if ($row === 0){
            echo "<form action='../func/edit_game.php' method='POST'>
            <input type='hidden' name='new_team' value='".$team."'>
            <input type='hidden' name='id_game' value='".$_POST['id_game']."'>
            <input class ='submit -addteam' type='submit' value='Создать команду'>
            </form>";
    }else{
        while ($row = $db_referal -> fetch_array()) {
            echo "<form action='../func/edit_game.php' method='POST'>
            <input class='input-team' type='text' name='team' value='".htmlspecialchars($row['team'], ENT_QUOTES)."' readonly>
            <input type='hidden' name='add_team' value='".$row['id']."'>
            <input type='hidden' name='id_game' value='".$_POST['id_game']."'>
            <input class ='submit -addteam' type='submit' value='Добавить'>
            </form>";
            }
            echo "<form action='../func/edit_game.php' method='POST'>
            <input type='hidden' name='new_team' value='".$team."'>
            <input type='hidden' name='id_game' value='".$_POST['id_game']."'>
            <input class ='submit -addteam' type='submit' value='Создать команду'>
            </form>";
    }
}

/* Замена команды в турнире */
if(!empty($_POST["change_team"])){

    $referal = "{$_POST['change_team']}%";
    $id_game = $_POST['id_game'];

    $types = $conn->query("SELECT type FROM games WHERE games.id = $id_game")->fetch_assoc();
    if($types['type'] == 'sat' OR $types['type'] == 'thu'){
        $type = 'main';
    }else{
        $type = $types['type'];
    }

    $stmt = $conn -> prepare("SELECT * from teams WHERE id != 1 AND date = year(curdate()) AND type = '$type' AND team LIKE (?)");
    $stmt->bind_param("s", $referal);
    $stmt->execute();
    $db_referal = $stmt->get_result();
   
    while ($row = $db_referal -> fetch_array()) {
        echo "<form action='../func/edit_game.php' method='POST'>
        <input class='input-team' type='text' name='team' value='".htmlspecialchars($row['team'], ENT_QUOTES)."' readonly>
        <input type='hidden' name='id_team' value='".$row['id']."'>
        <input type='hidden' name='change_team' value='".$_POST['id_team']."'>
        <input type='hidden' name='id_game' value='".$_POST['id_game']."'>
        <input class ='submit -addteam' type='submit' name='changeTeam' value='Заменить'>
        </form>";
    }
}

/* Изменение название команды или удаление */
if(!empty($_POST["edit_team"])){ //Принимаем данные

    $referal = "{$_POST['edit_team']}%";
    $type = $_POST['type_game'];

    $stmt = $conn -> prepare("SELECT * from teams WHERE id != 1 AND date = year(curdate()) AND team LIKE (?)");
    $stmt->bind_param("s", $referal);
    $stmt->execute();
    $db_referal = $stmt->get_result();
   
    while ($row = $db_referal -> fetch_array()) {
        echo "<form action='../func/edit_teams.php' method='POST'>
        <input class='input-team' type='text' name='team' value='".htmlspecialchars($row['team'], ENT_QUOTES)."' autocomplete='off'>
        <input class='input-team -type' type='text' value='".$row['type']."' readonly>
        
        <input type='hidden' name='id_team' value='".$row['id']."'>
        <input class ='submit -addteam' type='submit' name='edit_team' value='Изменить'>
        <input class ='submit -addteam' type='submit' name='del_team'value='Удалить'>
        </form>";
    }
}