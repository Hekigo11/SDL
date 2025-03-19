<?php
$isallok = true;
$msg = "";

if(trim($_POST['txtusername'])=='' && trim($_POST['txtpassword'])==''){
	$isallok = false; $msg ="Invalid Credentials";
}

if($isallok){
	include("dbconi.php");
	// Change yung Column and yung Table name since wala pang DB
	$query = "SELECT * FROM users WHERE user_id='".mysqli_real_escape_string($dbc, $_POST['txt_idno'])."' and password='".mysqli_real_escape_string($dbc, $_POST['txtpassword'])."'";
	$result = mysqli_query($dbc, $query);
	if(mysqli_num_rows($result)>0){ // if with records found
		$row = mysqli_fetch_array($result);
		session_start();
		// $_SESSION['WDDlogin'] = '1';
		// $_SESSION['WDDrole'] = $row['role_id'];
		// $_SESSION['WDDid'] = $row['user_id'];
		$msg = 'success';
	} else {
		$msg = 'failed';
	}
}
echo $msg;
?>