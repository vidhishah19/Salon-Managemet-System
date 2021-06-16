<?php

require './class/database1.php';

?>


<!DOCTYPE html>
<head>
<title>View Salon's Services</title>
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
		<div class="table-agile-info">
 <div class="panel panel-default">
    <div class="panel-heading">
     Salon Services
    </div>
     <a href="add-salon-service.php"><i class="glyphicon glyphicon-plus"></i>ADD</a>
    <div>
      <table class="table" ui-jq="footable" ui-options='{
        "paging": {
          "enabled": true
        },
        "filtering": {
          "enabled": true
        },
        "sorting": {
          "enabled": true
        }}'>
        <thead>
          <tr>
            <th class='col-md-1'>ID</th>
            <th>Salon</th>
            <th>Service</th>
            <th>Price</th>
            <th>Status</th>
            <th class='col-md-2'>Action</th>
          </tr> 
        </thead>
        <tbody>
<?php

if(isset($_GET['did']))
{
        $did = $_GET['did'];
    
    $deleteq = mysqli_query($connection, "delete from salon_service where salon_service_id='{$did}'") or die(mysqli_error($connection));
    
    if($deleteq)
    {
        echo "<script>alert('Record Deleted');</script>";
    }
}

$selectq= mysqli_query($connection, "SELECT
    `salon_service`.`salon_service_id`
    , `tbl_salon`.`salon_name`
    , `service_master`.`service_name`
    , `salon_service`.`price`
    , `salon_service`.`status`
FROM
    `tbl_salon`
    INNER JOIN `salon_service` 
        ON (`tbl_salon`.`salon_id` = `salon_service`.`salon_id`)
    INNER JOIN `service_master` 
        ON (`service_master`.`service_id` = `salon_service`.`service_id`)
ORDER BY `salon_service`.`salon_service_id` ASC;") or die(mysqli_error($connection));
$count= mysqli_num_rows($selectq);
echo $count . " Record(s) Found";
while($planrow= mysqli_fetch_array($selectq))
{
    echo "<tr>";
    echo "<td>{$planrow['salon_service_id']}</td>";
    echo "<td>{$planrow['salon_name']}</td>";
    echo "<td>{$planrow['service_name']}</td>";
    echo "<td>{$planrow['price']}</td>";
    echo "<td>{$planrow['status']}</td>";
    echo "<td><a href='edit-salon-service.php?eid={$planrow['salon_service_id']}'><img style='width:16px' src='images/edit.png'>Edit</a> | <a href='display-salon-service.php?did={$planrow['salon_service_id']}'><img style='width:16px' src='images/delete.png'>Delete</a></td>";
    echo "</tr>";
}
?>
<!--
          <tr>
            <td>2</td>
            <td>Elodia</td>
            <td>Weisz</td>
            <td>Wallpaperer Helper</td>
          
            <td>March 30th 1982</td>
          </tr>
-->
        </tbody>
      </table>
    </div>
     
  </div>
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
