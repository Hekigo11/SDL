<form id="frmlogin">
	<div class="form-group">
		<label for="txtusername">Username</label>
		<input type="text" class="form-control" id="txtusername" name="txtusername" placeholder="Enter Username">
	</div>
	<div class="form-group">
		<label for="txtpassword">Password</label>
		<input type="password" class="form-control" id="txtpassword" name="txtpassword" placeholder="Enter Password">
	</div>
	
	<button type="button" class="btn btn-primary" id="btnlogin">Login</button>

	<div class="text-center">
		<label>Dont have an account yet? <a href="modules/register.php">Register</a></label>
	</div>
</form>

<script>
	$(document).ready(function(){
		$("#btnlogin").click(function(){
			$.post("modules/login_req.php", $("form#frmlogin").serialize(), function(d){
				if(d=='success'){
					alert("Login Success");
				} else {
					alert(d);
				}
			});
		});
	});
</script>
	