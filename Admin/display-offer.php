<?php

require './class/database1.php';

?>


<!DOCTYPE html>
<head>
<title>View Offers</title>
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
     Offer Details
    </div>
     <a href="add-offer.php"><i class="glyphicon glyphicon-plus"></i>ADD</a>
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
            <th class='col-md-2'>Offer Type</th>
            <th>Offer Details</th>
            <th class='col-sm-2'>Action</th>
          </tr> 
        </thead>
        <tbody>
<?php

if(isset($_GET['did']))
{
        $did = $_GET['did'];
    
    $deleteq = mysqli_query($connection, "delete from tbl_offers where offer_id='{$did}'") or die(mysqli_error($connection));
    
    if($deleteq)
    {
        echo "<script>alert('Record Deleted');</script>";
    }
}

$selectq= mysqli_query($connection, "SELECT
    `tbl_offers`.`offer_id`
    , `tbl_salon`.`salon_name`
    , `tbl_offers`.`offer_type`
    , `tbl_offers`.`offer_details`
FROM
    `tbl_salon`
    INNER JOIN `tbl_offers` 
        ON (`tbl_salon`.`salon_id` = `tbl_offers`.`salon_id`)
ORDER BY `tbl_offers`.`offer_id` ASC;") or die(mysqli_error($connection));
$count= mysqli_num_rows($selectq);
echo $count . " Record(s) Found";
while($planrow= mysqli_fetch_array($selectq))
{
    echo "<tr>";
    echo "<td>{$planrow['offer_id']}</td>";
    echo "<td>{$planrow['salon_name']}</td>";
    echo "<td>{$planrow['offer_type']}</td>";
    echo "<td>{$planrow['offer_details']}</td>";
    echo "<td><a href='edit-offer.php?eid={$planrow['offer_id']}'><img style='width:16px' src='images/edit.png'>Edit</a> | <a href='display-offer.php?did={$planrow['offer_id']}'><img style='width:16px' src='images/delete.png'>Delete</a></td>";
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
