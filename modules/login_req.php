<?php
$isallok = true;
$msg = "";

if (trim($_POST['txtusername']) == '' || trim($_POST['txtpassword']) == '') {
    $isallok = false;
    $msg = "User not found.";
}

if($isallok){
	include("dbconi.php");
	//////$hash_login = password_hash($_POST['txtpassword'], PASSWORD_DEFAULT);

	$query = "SELECT * FROM users WHERE email_add = ? OR mobile_num = ?";
    $stmt = $dbc->prepare($query);
    $stmt->bind_param("ss", $_POST['txtusername'], $_POST['txtusername']);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

	// $query = "SELECT * FROM users
	// 		WHERE (email_add = '".mysqli_real_escape_string($dbc, $_POST['txtusername'])."'
	// 		OR mobile_num = '".mysqli_real_escape_string($dbc, $_POST['txtusername'])."')
	// 		AND password = '".mysqli_real_escape_string($dbc, $hash_login)."'";

	// $query = "SELECT * FROM users
	// 		WHERE email_add = '".mysqli_real_escape_string($dbc, $_POST['txtusername'])."'
	// 		OR mobile_num = '".mysqli_real_escape_string($dbc, $_POST['txtusername'])."'";

	// $result = mysqli_query($dbc, $query);
	// $row = mysqli_fetch_array($result);

	// if(mysqli_num_rows($result)>0 && password_verify($_POST['txtpassword'], $row['password'])){
	// 	$msg = "success";

	// 	session_start();
	// 	$_SESSION['loginok']='1';
	// }else{
	// 	$msg = "User not found.";
	// }
	if ($row && password_verify($_POST['txtpassword'], $row['password'])) {
        session_start();
        $_SESSION['loginok'] = '1';
        $msg = "success";
    } else {
        $msg = "User not found.";
    }
	// if (mysqli_num_rows($result)>0){
	// 	mysqli_fetch_array($result);
	// 	echo "success";

	// 	session_start();
	// 	$_SESSION['loginok']='1';
	// 	//$_SESSION['userfullname'] = $row['fullname'];
	// }else{
	// 	echo "User not found.";
	// }
}
echo $msg;
?>
