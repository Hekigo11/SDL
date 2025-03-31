<?php
$username = "root";
$password = "";
$server   = "localhost";
$dbasename = "marj";

$dbc = mysqli_connect($server, $username, $password);
mysqli_select_db($dbc, $dbasename);
?>