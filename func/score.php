<?php
require('../conf/dbconfig.php');
$id_game = $_GET['id'];
echo "<pre>";

/* Получаем id команд */
$final = $conn->query("SELECT IF (s1>s2, id_t2,id_t1) as teams, round FROM final WHERE final.id_game = $id_game");
$final_team = $final->fetch_assoc();
var_dump($final);

foreach ($final as $value){
    var_dump($value);
}