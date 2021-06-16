<?php
session_start();
require './class/database1.php';

if($_POST)
{
    $email=$_POST['email'];
    $password=$_POST['password'];
    
    $selectquery= mysqli_query($connection, "select * from tbl_salon where email='{$email}' and password='{$password}'") or die(mysqli_error($connection));
    $count= mysqli_num_rows($selectquery);
    $row= mysqli_fetch_array($selectquery);
    if($count>0)
    {
        $_SESSION['salonid']=$row['salon_id'];
        $_SESSION['salonname']=$row['salon_name'];
        
        echo "<script>alert('Welcome Back!');window.location='homepage.php';</script>";
        
    } else
    {
        echo "<script>alert('Salon details did not match. Please register!');</script>";
    }
}

?>
<!DOCTYPE html>
<head>
<title>Login Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="keywords" content="Visitors Responsive web template, Bootstrap Web Templates, Flat Web Templates, Android Compatible web template, 
Smartphone Compatible web template, free webdesigns for Nokia, Samsung, LG, SonyEricsson, Motorola web design" />
<script type="application/x-javascript"> addEventListener("load", function() { setTimeout(hideURLbar, 0); }, false); function hideURLbar(){ window.scrollTo(0,1); } </script>
<!-- bootstrap-css -->
<link rel="stylesheet" href="css/bootstrap.min.css" >
<!-- //bootstrap-css -->
<!-- Custom CSS -->
<link href="css/style.css" rel='stylesheet' type='text/css' />
<link href="css/style-responsive.css" rel="stylesheet"/>
<!-- font CSS -->
<link href='//fonts.googleapis.com/css?family=Roboto:400,100,100italic,300,300italic,400italic,500,500italic,700,700italic,900,900italic' rel='stylesheet' type='text/css'>
<!-- font-awesome icons -->
<link rel="stylesheet" href="css/font.css" type="text/css"/>
<link href="css/font-awesome.css" rel="stylesheet"> 
<!-- //font-awesome icons -->
<script src="js/jquery2.0.3.min.js"></script>
</head>
<body style="background-color: lightgray;">
    <br><br>
<div class="log-w3">
<div class="w3layouts-main">
	<h2>Log In</h2>
		<form action="#" method="post">
			<input type="email" class="ggg" name="email" placeholder="E-MAIL" required="">
			<input type="password" class="ggg" name="password" placeholder="PASSWORD" required="">
			<span><input type="checkbox" />Remember Me</span>
                        <h6><a href="forgotpassword.php">Forgot Password?</a></h6>
				<div class="clearfix"></div>
				<input type="submit" value="Sign In" name="login">
		</form>
        <p>Don't Have an Account ?<a class="active" href="registration.php">Create an account</a></p>
</div>
    <br><br><br>
</div>
    
<script src="js/bootstrap.js"></script>
<script src="js/jquery.dcjqaccordion.2.7.js"></script>
<script src="js/scripts.js"></script>
<script src="js/jquery.slimscroll.js"></script>
<script src="js/jquery.nicescroll.js"></script>
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="js/flot-chart/excanvas.min.js"></script><![endif]-->
<script src="js/jquery.scrollTo.js"></script>
</body>
</html>
