<?php
include("dbconi.php");
$query = "SELECT * FROM user
		WHERE username = '".mysqli_real_escape_string($dbc, $_POST['txtusername'])."'
		AND passw = '".mysqli_real_escape_string($dbc, $_POST['txtpassword'])."'";
$result = mysqli_query($dbc, $query);
$row = mysqli_fetch_array($result);
$username = "SELECT * FROM user 
			WHERE username = '".mysqli_real_escape_string($dbc, $_POST['txtusername'])."'";
$result2 = mysqli_query($dbc, $username);
if (mysqli_num_rows($result)>0){
	mysqli_fetch_array($result);
	echo "success";
	//session_start();
	//$_SESSION['loginok']='1';
	//$_SESSION['userfullname'] = $row['fullname'];
}else{
	echo "User not found.";
}		
?>
