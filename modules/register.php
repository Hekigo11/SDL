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
	<link rel="stylesheet" href="regverif.css">

	
  </head>

<body>

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
