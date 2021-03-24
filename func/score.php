<?php
require_once('../conf/dbconfig.php');
require_once('../conf/config.php');

for($i=1;$i<=count($_POST)/5;$i++){

    if(isset($_POST)){
        isset($_POST[$i]) ? $place = $_POST[$i] : $place = 0;
        isset($_POST['sat'.$i]) ? $sat = (int)$_POST['sat'.$i] : $sat = 0;
        isset($_POST['thu'.$i]) ? $thu = (int)$_POST['thu'.$i] : $thu = 0;
        isset($_POST['king'.$i]) ? $king = (int)$_POST['king'.$i] : $king = 0;
        isset($_POST['queen'.$i]) ? $queen = (int)$_POST['queen'.$i] : $queen = 0;
        isset($_POST['old'.$i]) ? ($old = (int)$_POST['old' . $i]) : $old = 0;
        isset($_POST['personal'.$i]) ? $personal = (int)$_POST['personal'.$i] : $personal = 0;
    }


    if ($organization == 'minsk') {
        $conn->query("UPDATE `$dbt_score` SET `sat`='$sat', `thu`='$thu', `old`='$old',  `king`='$king', `queen`='$queen' WHERE (`place`= $place)");
    }else{
        $conn->query("UPDATE `$dbt_score` SET `sat`='$sat', `thu`='$thu', `personal`='$personal', `king`='$king', `queen`='$queen' WHERE (`place`= $place)");
    }

}

header('Location: ../admin/score.php');
exit;