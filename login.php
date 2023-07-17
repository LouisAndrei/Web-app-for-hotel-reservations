<?php include('server.php') ?>
<!DOCTYPE html>
<html>
<head>
  <title>Sistem de login/register</title>
  <link rel="stylesheet" type="text/css" href="css/login_css.css">
</head>
<body>
  <div class="header1">
  	<h2>Login</h2>
  </div>
	 
  <form method="post" action="login.php">
  	<?php include('errors.php'); ?>
  	<div class="input-group">
  		<label>Nume de utilizator</label>
  		<input type="text" name="username" >
  	</div>
  	<div class="input-group">
  		<label>Parola</label>
  		<input type="password" name="password">
  	</div>
  	<div class="input-group">
  		<button type="submit" class="btn" name="login_user">Login</button>
  	</div>
  	<p>
  		Nu aveti inca un cont? <a href="register.php">Sign up</a>
  	</p>
  </form>
</body>
</html>