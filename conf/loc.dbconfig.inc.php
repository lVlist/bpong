<?php
$dbhost = "localhost";
$dbuser = "root";
$dbpass = "";
$dbname = "bpong";

$conn = new mysqli ($dbhost, $dbuser, $dbpass, $dbname);

if ($conn->connect_error)
    die ("Ошибка подключения: ". $conn->connect_error);
?>