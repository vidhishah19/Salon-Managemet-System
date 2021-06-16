<?php

require './class/database1.php';

if(isset($_POST['submitbtn']))
{
    $sname= mysqli_real_escape_string($connection, $_POST['sname']);
    $oname= mysqli_real_escape_string($connection, $_POST['oname']);
    $semail= mysqli_real_escape_string($connection, $_POST['semail']);
    $spassword= mysqli_real_escape_string($connection, $_POST['spassword']);
    $sphone= mysqli_real_escape_string($connection, $_POST['sphone']);
    $spicture= "uploads/".$_FILES['spicture']['name'];
    $scertificate= "uploads/".$_FILES['scertificate']['name'];
    $saddress= mysqli_real_escape_string($connection, $_POST['saddress']);
    
$query= mysqli_query($connection, "insert into tbl_salon(salon_name,owner_name,email,password,phone_no,image,certificate,address) values('{$sname}','{$oname}','{$semail}','{$spassword}','{$sphone}','{$spicture}','{$scertificate}','{$saddress}')") or die(mysqli_error($connection));


//If Query Executed
if($query)
{
    $fileprocess= move_uploaded_file($_FILES['spicture']['name'], $spicture);
    $certiprocess= move_uploaded_file($_FILES['scertificate']['name'], $scertificate);
}
if($query)
{
    echo "<script>alert('Record Inserted');</script>";
}

}
?>
<!DOCTYPE html>
<head>
<title>Add Salon Form</title>
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
                            Add Salon
                        </header>
                        <div class="panel-body">
                            <div class="position-center">
                                <form role="form" method="post" enctype="multipart/form-data">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Salon Name:</label>
                                    <input type="text" class="form-control"  name="sname" placeholder="Enter Salon's Name" required>
                                </div>
                                    
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Owner Name:</label>
                                    <input type="text" class="form-control" id="exampleInputEmail1" name="oname" placeholder="Enter Owner's Name" required>
                                </div>
                                    
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Email:</label>
                                    <input type="email" class="form-control" id="exampleInputEmail1" name="semail" placeholder="Enter Email" required>
                                </div>
                                    
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Password:</label>
                                    <input type="password" class="form-control" id="exampleInputPassword1" name="spassword" placeholder="Enter Password" required>
                                </div>
                                    
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Phone No.:</label>
                                    <input type="text" class="form-control" id="exampleInputEmail1" name="sphone" placeholder="Enter Phone No." required>
                                </div>
                                    
                                <div class="form-group">
                                    <label for="exampleInputFile">Profile Picture:</label>
                                    <!--<input type="text" class="form-control" id="exampleInputEmail1" name="spicture" placeholder="Enter Profile Picture" required>-->
                                    <input type="file" id="exampleInputFile" name="spicture" accept="image/*" required>
                                    <p class="help-block">(*jpg,*png,*jpeg)<br>Please upload your image here.</p>
                                </div>
                                    
                                    
                                <div class="form-group">
                                    <label for="exampleInputFile">Certificate:</label>
                                    <!--<input type="text" class="form-control" id="exampleInputEmail1" name="scertificate" placeholder="Enter Certificate">-->
                                    <input type="file" id="exampleInputFile" name="scertificate" accept="image/*">
                                    <p class="help-block">(*jpg,*png,*jpeg)<br>Please upload your certificate here (If any).</p>
                                </div>
                                    
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Address:</label>
                                    <div>
                                        <textarea class="form-control" name="saddress" required></textarea>
                                    </div>
                                </div>
                                   
                                <button type="submit" name="submitbtn" class="btn btn-success">Submit</button>
                                <button type="reset" class="btn btn-danger">Reset</button>
                                <button type="button" onclick="window.location='display-salon.php';" class="btn btn-info">View</button>
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