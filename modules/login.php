<form id="frmlogin">
	<div class="form-group text-center">
		<label for="txtusername">Email or Mobile Number</label>
		<input type="text" class="form-control rounded-pill" id="txtusername" name="txtusername" placeholder="Enter Registered Email or Mobile Number">
	</div>
	<div class="form-group text-center">
		<label for="txtpassword">Password</label>
		<input type="password" class="form-control rounded-pill" id="txtpassword" name="txtpassword" placeholder="Enter Password">
	</div>
	<div class="d-flex justify-content-around align-items-center">
	<button type="button" class="btn btn-primary rounded-pill w-25" id="btnlogin">Login</button>
	<button type="button" class="btn btn-outline-secondary rounded-pill w-25" id="btnCancel" data-dismiss="modal" aria-label="Close">Cancel</button>
	</div>
	<div class="text-center mx-2">
	<label for="#" > <a href=""> Forgot Password?</a>
	</label>
	</div>
	
	<div class="text-center mx-2"> 
		<label>Dont have an account yet? <a href="modules/register.php">Register</a></label>
	</div>
</form>

<script>
	$(document).ready(function(){
		$("#btnlogin").click(function(){
			$.post("modules/login_req.php", $("form#frmlogin").serialize(), function(d){
				if(d=='success'){
					alert("Login Success");
					$("#txtusername").val('');
                    $("#txtpassword").val('');
				} else {
					alert(d);
				}
			});
		});

		$("#btnCancel").click(function(){
            $("#txtusername").val('');
            $("#txtpassword").val('');
        });
	});
</script>
	