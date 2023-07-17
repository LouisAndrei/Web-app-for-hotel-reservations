<?php include('server.php') ?>
<!DOCTYPE html>
<html>
<head>
  <title>Sistem de login/register</title>
  <link rel="stylesheet" type="text/css" href="css/login_css.css">
</head>
<body>
  <div class="header1">
  	<h2>Register</h2>
  </div>
	
  <form method="post" action="register.php">
  	<?php include('errors.php'); ?>
  	<div class="input-group">
  	  <label>Nume de utilizator</label>
  	  <input type="text" name="username" value="<?php echo $username; ?>">
  	</div>
  	<div class="input-group">
  	  <label>Email</label>
  	  <input type="email" name="email" value="<?php echo $email; ?>">
  	</div>
  	<div class="input-group">
  	  <label>Parola</label>
  	  <input type="password" name="password_1">
  	</div>
  	<div class="input-group">
  	  <label>Confirma parola</label>
  	  <input type="password" name="password_2">
  	</div>
  	<div class="input-group">
  	  <button type="submit" class="btn" name="reg_user">Register</button>
  	</div>
  	<p>
  		Aveti deja un cont? <a href="login.php">Sign in</a>
  	</p>
  </form>
</body>
</html>