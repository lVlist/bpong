<?php
require('../conf/dbconfig.php');

if(!empty($_POST["referal"])){ //Принимаем данные

    $referal = "{$_POST['referal']}%";
    $stmt = $conn -> prepare("SELECT * from teams WHERE id != 1 AND date = year(curdate()) AND team LIKE (?)");
    $stmt->bind_param("s", $referal);
    $stmt->execute();
    $db_referal = $stmt->get_result();
    $row = $db_referal->num_rows;
    $team = htmlspecialchars($_POST["referal"], ENT_QUOTES);
    
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
?>