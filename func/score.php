<?php
require_once('../conf/dbconfig.php');
require_once('../conf/config.php');

for($i=1;$i<=count($_POST)/5;$i++){
    $place = $_POST[$i];
    
    $sat = (int)$_POST['sat'.$i];
    $thu = (int)$_POST['thu'.$i];
    $king = (int)$_POST['king'.$i];
    $queen = (int)$_POST['queen'.$i];
    $old = (int)$_POST['old'.$i];
    $personal = (int)$_POST['personal'.$i];

    if ($organization == 'minsk') {
        $conn->query("UPDATE `$dbt_score` SET `sat`='$sat', `thu`='$thu', `old`='$old',  `king`='$king', `queen`='$queen' WHERE (`place`= $place)");
    }else{
        $conn->query("UPDATE `$dbt_score` SET `sat`='$sat', `thu`='$thu', `personal`='$personal', `king`='$king', `queen`='$queen' WHERE (`place`= $place)");
    }

}

header('Location: ../admin/score.php');
exit;