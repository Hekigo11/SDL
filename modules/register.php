<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

	<!-- jQuery library -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

	<!-- Popper JS -->
	<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>

	<!-- Latest compiled JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
	<title>Register</title>
	<link rel="stylesheet" href="../vendor/style1.css">

	<style>
		@import url('https://fonts.googleapis.com/css?family=Noto%20Sans%20Coptic:700|Noto%20Sans%20Coptic:400');
@import url('https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&family=Oswald:wght@200..700&display=swap');
:root{
--text: #040316;
--background: #e3e3ed;
--primary1: #21233f;
--secondary1: #feebd2;
--accent: #176ca1;
--linearPrimarySecondary: linear-gradient(#21233f, #feebd2);
--linearPrimaryAccent: linear-gradient(#21233f, #176ca1);
--linearSecondaryAccent: linear-gradient(#feebd2, #176ca1);
--radialPrimarySecondary: radial-gradient(#21233f, #feebd2);
--radialPrimaryAccent: radial-gradient(#21233f, #176ca1);
--radialSecondaryAccent: radial-gradient(#feebd2, #176ca1);
}
body {
    font-family: 'Noto Sans Coptic';
    font-weight: 400;
  }
 
	</style>

  </head>

<body style="background: var(--primary1); 
display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0;">

      <div class="card mx-auto col-md-5 my-3"  style="border-radius: 30px; background-color: var(--background);">
          <div class="card-body">
              <h5 class="card-title">Register</h5>
              <form id="frmstud" enctype="multipart/form-data">

			  	<div class="form-group">
                    <label for="txtstudno">Email</label>
                    <input type="text" class="form-control" id="txtemail" name="txtemail">
                </div>
            	<div class="form-group">
                    <label for="txtfname">First Name</label>
                    <input type="text" class="form-control" id="txtfname" name="txtfname">
                </div>
                <div class="form-group">
                    <label for="txtmname">Middle Name</label>
                    <input type="text" class="form-control" id="txtmname" name="txtmname">
                </div>
                <div class="form-group">
                    <label for="txtlname">Last Name</label>
                    <input type="text" class="form-control" id="txtlname" name="txtlname">
                </div>
                
                <div class="form-group">
                    <label for="txtstudno">Mobile Number</label>
                    <input type="text" class="form-control" id="txtmobilenum" name="txtmobilenum">
                </div>
                <div class="form-group">
                    <label for="txtstudno">Password</label>
                    <input type="password" class="form-control" id="txtpassword" name="txtpassword">
                </div>

                <button type="button" class="btn btn-primary" id="btnsave">Save</button>
                <button type="button" class="btn btn-secondary" id="btncancel">Cancel</button>
              </form>
          </div>
      </div>
</body>

<script>
$(document).ready(function(){
	$("#btncancel").click(function(){
		document.location = "../index.php";
	});
	
	$("#btnsave").click(function(){
		$.post("register_save.php", $("form#frmstud").serialize(), function(d){
			if(d == 'success'){
				alert("Registration successful! Please check your email for verification code.");
				window.location.href = "verify.php";
			} else {
				alert(d);
			}
		});
	});
});
</script>
