<?php
$isallok = true;
$msg = "";

if(trim($_POST['txtfname'])==''){
	$isallok = false; $msg .="Enter Firstname\n";
}
if(trim($_POST['txtmname'])==''){
	$isallok = false; $msg .="Enter Middlename\n";
}
if(trim($_POST['txtlname'])==''){
	$isallok = false; $msg .="Enter Lastname\n";
}

if(trim($_POST['txtemail'])==''){
	$isallok = false; $msg .="Enter Email\n";
}

if(trim($_POST['txtusername'])==''){
	$isallok = false; $msg .="Enter Username\n";
}

if(trim($_POST['txtpassword'])==''){
	$isallok = false; $msg .="Enter Password\n";
}

if($isallok){
	include("dbconi.php");
	$query = "INSERT INTO user (firstname, middlename, lastname, email, username, passw) VALUES (
        '".mysqli_real_escape_string($dbc, $_POST['txtfname'])."',
        '".mysqli_real_escape_string($dbc, $_POST['txtmname'])."',
        '".mysqli_real_escape_string($dbc, $_POST['txtlname'])."',
        '".mysqli_real_escape_string($dbc, $_POST['txtemail'])."',
        '".mysqli_real_escape_string($dbc, $_POST['txtusername'])."',
        '".mysqli_real_escape_string($dbc, $_POST['txtpassword'])."'
    )";

	$result = mysqli_query($dbc, $query);
    mysqli_close($dbc);
    $msg = "success";
	echo $msg;
    
} else {
	echo $msg;
}
?>