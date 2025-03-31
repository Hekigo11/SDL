<?php
$isallok = true;
$msg = "";

if(trim($_POST['txtfname'])==''){
	$isallok = false; $msg .="Enter Firstname\n";
}
// if(trim($_POST['txtmname'])==''){
// 	$isallok = false; $msg .="Enter Middlename\n";
// }
if(trim($_POST['txtlname'])==''){
	$isallok = false; $msg .="Enter Lastname\n";
}

if(trim($_POST['txtemail'])==''){
	$isallok = false; $msg .="Enter Email\n";
}

if(trim($_POST['txtusername'])==''){
	$isallok = false; $msg .="Enter Mobile Number\n";
}

if(trim($_POST['txtpassword'])==''){
	$isallok = false; $msg .="Enter Password\n";
}

if($isallok){
	include("dbconi.php");
	$hash_reg = password_hash($_POST['txtpassword'], PASSWORD_DEFAULT);
	
	$query = "INSERT INTO users (fname, mname, lname, email_add, mobile_num, password, role_id) VALUES (
        '".mysqli_real_escape_string($dbc, $_POST['txtfname'])."',
        '".mysqli_real_escape_string($dbc, $_POST['txtmname'])."',
        '".mysqli_real_escape_string($dbc, $_POST['txtlname'])."',
        '".mysqli_real_escape_string($dbc, $_POST['txtemail'])."',
        '".mysqli_real_escape_string($dbc, $_POST['txtusername'])."',
		'".mysqli_real_escape_string($dbc, $hash_reg)."',
		2
    )";

	$result = mysqli_query($dbc, $query);
    mysqli_close($dbc);
    $msg = "success";
	echo $msg;
    
} else {
	echo $msg;
}
?>