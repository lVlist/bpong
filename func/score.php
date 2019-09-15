<?php
require_once('../conf/dbconfig.php');

for($i=1;$i<=count($_POST)/5;$i++){
    $place = $_POST[$i];
    
    $sat = $_POST['sat'.$i];
    $thu = $_POST['thu'.$i];
    $king = $_POST['king'.$i];
    $queen = $_POST['queen'.$i];
    $conn->query("UPDATE `$dbt_score` SET `sat`='$sat', `thu`='$thu', `king`='$king', `queen`='$queen' WHERE (`place`= $place)");
}

header('Location: ../admin/score.php');