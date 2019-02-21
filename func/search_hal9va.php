<?php
session_start();
require('../conf/dbconfig.php');
if(!empty($_POST["referal"])){ //Принимаем данные

    $referal = "{$_POST['referal']}%";
    $stmt = $conn -> prepare("SELECT * from teams WHERE id != 1 AND date = year(curdate()) AND team LIKE (?)");
    $stmt->bind_param("s", $referal);
    $stmt->execute();
    $db_referal = $stmt->get_result();
   
    while ($row = $db_referal -> fetch_array()) {
        echo "<form action='../func/hal9va.php' method='POST'>
        <input class='input-team' type='text' name='team' value='".htmlspecialchars($row['team'], ENT_QUOTES)."' readonly>
        <input type='hidden' name='id_team' value='".$row['id']."'>
        <input type='hidden' name='id_game' value='".$_SESSION['id_game']."'>
        <input class ='submit -addteam' type='submit' name='edit_hal9va' value='Заменить'>
        </form>";
    }
}
?>