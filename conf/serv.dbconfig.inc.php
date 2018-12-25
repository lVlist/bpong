<?php
$dbhost = "mysql.zzz.com.ua";
$dbuser = "mist";
$dbpass = "Dfc.rjd123b";
$dbname = "mist";

$conn = new mysqli ($dbhost, $dbuser, $dbpass, $dbname);

if ($conn->connect_error)
    die ("Ошибка подключения: ". $conn->connect_error);
?>