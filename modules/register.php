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
	
  </head>
<body>
<body>
      <div class="card mx-auto col-md-5 my-3">
          <div class="card-body">
              <h5 class="card-title">Register</h5>
              <form id="frmstud" enctype="multipart/form-data">
              <div class="form-group">
                    <label for="txtfname">Firstname</label>
                    <input type="text" class="form-control" id="txtfname" name="txtfname">
                </div>
                <div class="form-group">
                    <label for="txtmname">Middlename</label>
                    <input type="text" class="form-control" id="txtmname" name="txtmname">
                </div>
                <div class="form-group">
                    <label for="txtlname">Lastname</label>
                    <input type="text" class="form-control" id="txtlname" name="txtlname">
                </div>
                <div class="form-group">
                    <label for="txtstudno">Email</label>
                    <input type="text" class="form-control" id="txtemail" name="txtemail">
                </div>
                <div class="form-group">
                    <label for="txtstudno">Mobile Number</label>
                    <input type="text" class="form-control" id="txtusername" name="txtusername">
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
		$.post("register_save.php",$("form#frmstud").serialize(), function(d){
			if(d=='success'){
				alert("Successfully Saved");
                document.location = "../index.php";
			} else {
				alert(d);
			}
		});
		
		// var form = $('form#frmstud')[0]; 
		// var formData = new FormData(form);
		
		// $.ajax({
		// 	url: "modules/add_save.php",
		// 	type: 'post',
		// 	data: formData,
		// 	contentType: false,
		// 	processData: false,
		// 	success: function(d){
		// 		if(d=='success'){
		// 			alert("Successfully Saved");
		// 			document.location = "./";
		// 		} else {
		// 			alert(d);
		// 		}
		// 	},
		// });
	});
});
</script>
