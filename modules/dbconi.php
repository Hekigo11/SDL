<?php
//connect to db
// papaltan pa yung dbname
$username = "root";
$password = "";
$server   = "localhost";
$dbasename = "cruz_sample";

$dbc = mysqli_connect($server, $username, $password);
mysqli_select_db($dbc, $dbasename);
?>