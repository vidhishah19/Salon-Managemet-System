<?php

require './class/database1.php';

$editid=$_GET['eid'];

if(!isset($_GET['eid']) || empty($_GET['eid']))
{
    //header("location:display-user.php");
}
$selectq= mysqli_query($connection, "select * from tbl_user where user_id='{$editid}'") or die(mysqli_error($connection));
$selectrow= mysqli_fetch_array($selectq);

if($_POST)
{
    $uid=$_POST['id'];
    $uname= mysqli_real_escape_string($connection, $_POST['uname']);
    $ugender= mysqli_real_escape_string($connection, $_POST['ugender']);
    $uemail= mysqli_real_escape_string($connection, $_POST['uemail']);
    $upassword= mysqli_real_escape_string($connection, $_POST['upassword']);
    $uphone= mysqli_real_escape_string($connection, $_POST['uphone']);
    $upicture= "uploads/".$_FILES['upicture']['name'];
    $uaddress= mysqli_real_escape_string($connection, $_POST['uaddress']);
    $udob= mysqli_real_escape_string($connection, $_POST['udob']);
    
$query= mysqli_query($connection, "insert into tbl_user(user_name,gender,email,password,phone_no,photo,address,dob) values('{$uname}','{$ugender}','{$uemail}','{$upassword}','{$uphone}','{$upicture}','{$uaddress}','{$udob}')") or die(mysqli_error($connection));

if($query)
{
    echo "<script>alert('Record Inserted');</script>";
}

}
?>
<!DOCTYPE html>
<head>
<title>Edit User</title>
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
<body>
<section id="container">
<!--header start-->
<header class="header fixed-top clearfix">
<!--logo start-->

<!--logo end-->
<?php
    include './basictheme/header_part.php';
?>
</header>
<!--header end-->
<!--sidebar start-->
<?php
    include './basictheme/side_part.php';
?>
<!--sidebar end-->
<!--main content start-->
<section id="main-content">
	<section class="wrapper">
	<div class="form-w3layouts">
        <!-- page start-->
        <!-- page start-->
        <div class="row">
            <div class="col-lg-12">
                    <section class="panel">
                        <header class="panel-heading">
                            Edit User
                        </header>
                        <div class="panel-body">
                            <div class="position-center">
                                <form role="form" method="post" enctype="multipart/form-data">
                                    <input type="hidden" name="uid" value="<?php echo $selectrow['user_id'] ?>">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">User Name:</label>
                                    <input type="text" class="form-control" id="exampleInputEmail1" value="<?php echo $selectrow['user_name'] ?>" name="uname" placeholder="Enter User's Name" required>
                                </div>
                                    
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Gender:</label>
                                    <input type="text" class="form-control" id="exampleInputEmail1" value="<?php echo $selectrow['gender'] ?>" name="ugender" placeholder="Enter User's Gender" required>
                                </div>
                                    
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Email:</label>
                                    <input type="email" class="form-control" id="exampleInputEmail1" value="<?php echo $selectrow['email'] ?>" name="uemail" placeholder="Enter Email" required>
                                </div>
                                    
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Password:</label>
                                    <input type="password" class="form-control" id="exampleInputPassword1" value="<?php echo $selectrow['password'] ?>" name="upassword" placeholder="Enter Password" required>
                                </div>
                                    
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Phone No.:</label>
                                    <input type="text" class="form-control" id="exampleInputEmail1" value="<?php echo $selectrow['phone_no'] ?>" name="uphone" placeholder="Enter Phone No." required>
                                </div>
                                    
                                <div class="form-group">
                                    <label for="exampleInputFile">Profile Picture:</label>
                                    <!--<input type="text" class="form-control" id="exampleInputEmail1" name="upicture" placeholder="Enter Profile Picture" required>-->
                                    <input type="file" id="exampleInputFile" value="<?php echo $selectrow['photo'] ?>" name="upicture" required>
                                    <p class="help-block">Please upload your image here.</p>
                                </div>
                                    
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Address:</label>
                                    <div>
                                        <textarea class="form-control" name="uaddress"><?php echo $selectrow['address'] ?></textarea>
                                    </div>
                                </div>
                                    
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Date Of Birth:</label>
                                    <input type="date" class="form-control" id="exampleInputEmail1" value="<?php echo $selectrow['dob'] ?>" name="udob" placeholder="Enter User's DateOfBirth" required>
                                </div>
                                   
                                <button type="submit" class="btn btn-success">Submit</button>
                                <button type="reset" class="btn btn-danger">Reset</button>
                                <button type="button" onclick="window.location='display-user.php';" class="btn btn-info">View</button>
                            </form>
                            </div>

                        </div>
                    </section>

            </div>
        </div>


        <!-- page end-->
        </div>
</section>

</section>

<!--main content end-->
</section>
<script src="js/bootstrap.js"></script>
<script src="js/jquery.dcjqaccordion.2.7.js"></script>
<script src="js/scripts.js"></script>
<script src="js/jquery.slimscroll.js"></script>
<script src="js/jquery.nicescroll.js"></script>
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="js/flot-chart/excanvas.min.js"></script><![endif]-->
<script src="js/jquery.scrollTo.js"></script>
</body>
</html>