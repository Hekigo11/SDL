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
</form>

<script>
	$(document).ready(function(){
		$("#btnlogin").click(function(){
			$.post("login_req.php", $("form#frmlogin").serialize(), function(d){
				if(d=='success'){
					document.location = "./welcome.php";
				} else {
					alert(d);
				}
			});
		});
	});
</script>
	