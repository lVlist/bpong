<?php
require('../conf/dbconfig.php');
if(!empty($_POST["referal"])){ //Принимаем данные

    $referal = "{$_POST['referal']}%";
    $stmt = $conn -> prepare("SELECT * from teams WHERE id != 1 AND date = year(curdate()) AND team LIKE (?)");
    $stmt->bind_param("s", $referal);
    $stmt->execute();
    $db_referal = $stmt->get_result();
   
    while ($row = $db_referal -> fetch_array()) {
        echo "<form action='../func/teams.php' method='POST'>
        <input class='input-team' type='text' name='team' value='".htmlspecialchars($row['team'], ENT_QUOTES)."'>
        <input type='hidden' name='id_team' value='".$row['id']."'>
        <input class ='submit -addteam' type='submit' name='edit_team' value='Изменить'>
        <input class ='submit -addteam' type='submit' name='del_team'value='Удалить'>
        </form>";
    }
}
?>