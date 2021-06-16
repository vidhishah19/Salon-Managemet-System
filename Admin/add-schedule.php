<?php

require './class/database1.php';

if($_POST)
{
    $aid= mysqli_real_escape_string($connection, $_POST['aid']);
    $sid= mysqli_real_escape_string($connection, $_POST['sid']);
    $stime= mysqli_real_escape_string($connection, $_POST['stime']);
    $etime= mysqli_real_escape_string($connection, $_POST['etime']);
    
$query= mysqli_query($connection, "insert into tbl_schedule(appointment_id,salon_id,start_time,end_time) values('{$aid}','{$sid}','{$stime}','{$etime}')") or die(mysqli_error($connection));

if($query)
{
    echo "<script>alert('Record Inserted');</script>";
}

}
?>
<!DOCTYPE html>
<head>
<title>Add Schedule</title>
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
                            Add Schedule
                        </header>
                        <div class="panel-body">
                            <div class="position-center">
                                <form role="form" method="post" enctype="multipart/form-data">
                                    
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Appointment ID:</label>
                                    <?php 
                                    $q = mysqli_query($connection,"select * from tbl_appointment") or die(mysqli_error($connection));
                                    echo "<select name='aid'>";
                                    while($arow= mysqli_fetch_array($q))
                                    {
                                        echo "<option value={$arow['appointment_id']}>{$arow['appointment_id']}</option>";
                                    }
                                    echo "</select>";
                                    ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Salon ID:</label>
                                    <?php 
                                    $q2 = mysqli_query($connection,"select * from tbl_salon") or die(mysqli_error($connection));
                                    echo "<select name='sid'>";
                                    while($srow= mysqli_fetch_array($q2))
                                    {
                                        echo "<option value={$srow['salon_id']}>{$srow['salon_name']}</option>";
                                    }
                                    echo "</select>";
                                    ?>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Start Time:</label>
                                    <input type="datetime-local" class="form-control" name="stime" placeholder="Enter Start Time" required>
                                </div>
                                <div class="form-group">
                                    <label for="exampleInputEmail1">End Time:</label>
                                    <input type="datetime-local" class="form-control" name="etime" placeholder="Enter End Time" required>
                                </div>
                                
                                <button type="submit" class="btn btn-success">Submit</button>
                                <button type="reset" class="btn btn-danger">Reset</button>
                                <button type="button" onclick="window.location='display-schedule.php';" class="btn btn-info">View</button>
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