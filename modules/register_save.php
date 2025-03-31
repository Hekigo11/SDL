<?php
$isallok = true;
$msg = "";
include("dbconi.php");

$email = mysqli_real_escape_string($dbc, $_POST['txtemail']);
$mo_num = mysqli_real_escape_string($dbc, $_POST['txtmobilenum']);

$query_check = "SELECT * FROM users WHERE email_add = '$email' OR mobile_num = '$mo_num'";
$result_check = mysqli_query($dbc, $query_check);

while ($row = mysqli_fetch_assoc($result_check)) {
    if ($row['email_add'] == $email) {
        $isallok = false; $msg .= "Email Already Exist!\n";
    }
	
    if ($row['mobile_num'] == $mo_num) {
        $isallok = false; $msg .= "Mobile Number Already Exist!\n";
    }
}

if(trim($_POST['txtfname'])==''){
	$isallok = false; $msg .="Enter Firstname\n";
}

if(trim($_POST['txtlname'])==''){
	$isallok = false; $msg .="Enter Lastname\n";
}

if(trim($_POST['txtemail'])==''){
	$isallok = false; $msg .="Enter Email\n";
}

if(trim($_POST['txtmobilenum'])==''){
	$isallok = false; $msg .="Enter Mobile Number\n";
}

if(trim($_POST['txtpassword'])==''){
	$isallok = false; $msg .="Enter Password\n";
}

if($isallok){
	$hash_reg = password_hash($_POST['txtpassword'], PASSWORD_DEFAULT);
	
	$query = "INSERT INTO users (fname, mname, lname, email_add, mobile_num, password, role_id) VALUES (
        '".mysqli_real_escape_string($dbc, $_POST['txtfname'])."',
        '".mysqli_real_escape_string($dbc, $_POST['txtmname'])."',
        '".mysqli_real_escape_string($dbc, $_POST['txtlname'])."',
        '".mysqli_real_escape_string($dbc, $_POST['txtemail'])."',
        '".mysqli_real_escape_string($dbc, $_POST['txtmobilenum'])."',
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