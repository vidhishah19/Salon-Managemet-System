<?php
$connection = mysqli_connect("localhost", "root", "", "sdp_database");
?>
<!DOCTYPE html>
<html lang="zxx">

    <!-- Mirrored from templates.hibootstrap.com/jeel/default/shop.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 12 Jan 2021 17:36:03 GMT -->
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <link rel="stylesheet" href="assets/css/bootstrap.min.css">

        <link rel="stylesheet" href="assets/css/boxicons.min.css">

        <link rel="stylesheet" href="assets/css/meanmenu.css">

        <link rel="stylesheet" href="assets/css/animate.min.css">

        <link rel="stylesheet" href="assets/css/owl.carousel.min.css">
        <link rel="stylesheet" href="assets/css/owl.theme.default.min.css">

        <link rel="stylesheet" href="assets/css/modal-video.min.css">

        <link rel="stylesheet" href="assets/css/odometer.min.css">

        <link rel="stylesheet" type="text/css" href="assets/css/settings.css">
        <link rel="stylesheet" type="text/css" href="assets/css/layers.css">
        <link rel="stylesheet" type="text/css" href="assets/css/navigation.css">

        <link rel="stylesheet" href="assets/css/nice-select.min.css">

        <link rel="stylesheet" href="assets/css/style.css">

        <link rel="stylesheet" href="assets/css/responsive.css">
        <title>Salon List</title>
        <link rel="icon" type="image/png" href="assets/images/favicon.png">
    </head>
    <body>

        <?php
        include './top_menu.php';
        ?>
        <section class="shop-area two pb-70">

        <div class="page-title-wrap">
            <div class="page-title-area title-img-four">
                <div class="title-shape">
                    <img src="assets/images/title/title-shape1.png" alt="Shape">
                </div>
                <div class="d-table">
                    <div class="d-table-cell">
                        <div class="title-content">
                            <h2>Salon List</h2>
                            <ul>
                                <li>
                                    <a href="home_page.php">Home</a>
                                </li>
                                <li>
                                    <span>Salon List</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="shop-page-area ptb-100">
            <div class="shop-shape">
            </div>
            <div class="container">
                <div class="shop-result">
                    <form>
                        <div class="row align-items-center">
                            <div class="col-lg-6">
                                <div class="result-content">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <input type="text" class="form-control" placeholder="Search">
                                    <button type="submit" class="btn">
                                        <i class='bx bx-search'></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <select>
                                        <option>Short By Popular</option>
                                        <option>Some option</option>
                                        <option>Another option</option>
                                        <option>Potato</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="row">
                  
                    
                        <?php 
                        $q = mysqli_query($connection, "select * from tbl_salon");
                        
                        while($data = mysqli_fetch_array($q)){
                            
                            echo "<div class='col-sm-6 col-lg-4'>
<div class='shop-item'>
<div class='top'>
<img src='http://localhost/Admin/$data[6]' style='width:250px;height:150px;' alt='Shop'>
<ul>
<li>
</a>
</li>
<li>
</a>
</li>
</ul>
</div>
<div class='bottom'>
<h3>
<a href='single-product.html'>$data[1]</a>
</h3>
<span>Rs.$data[2]</span>
</div>
</div>
</div>";
                        }
                        /*   echo '
                        <div class="shop-item">';
                         echo " <div class='top'>
                                <img src='http://localhost/Admin/$data[6]' alt='Shop'>
                                <ul>
                                    <li>
                                        <a href='cart.html' target='_blank'>
                                            <i class='bx bx-cart'></i>
                                        </a>
                                    </li>
                                    <li>
                                        <a href='wishlist.html' target='_blank'>
                                            <i class='bx bxs-heart'></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>";   
                         echo "<div class='bottom'>
                                <h3>
                                    <a href='single-product.html'>$data[1]</a>
                                </h3>
                                <span>Rs.$data[2]</span>
                            </div>";
                        }
                         */
                         
                        ?>
                           
                            
                     
                    
                </div>
                
            </div>
</section>
        <?php
        include './footer.php';
        ?>
<div class="go-top">
            <i class='bx bxs-up-arrow-alt'></i>
            <i class='bx bxs-up-arrow-alt'></i>
</div>


        <script data-cfasync="false" src="../../cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script><script src="assets/js/jquery-3.5.1.min.js"></script>
        <script src="assets/js/popper.min.js"></script>
        <script src="assets/js/bootstrap.min.js"></script>

        <script src="assets/js/form-validator.min.js"></script>

        <script src="assets/js/contact-form-script.js"></script>

        <script src="assets/js/jquery.ajaxchimp.min.js"></script>

        <script src="assets/js/jquery.meanmenu.js"></script>

        <script src="assets/js/wow.min.js"></script>

        <script src="assets/js/owl.carousel.min.js"></script>

        <script src="assets/js/jquery-modal-video.min.js"></script>

        <script src="assets/js/odometer.min.js"></script>
        <script src="assets/js/jquery.appear.min.js"></script>

        <script src="assets/js/jquery.themepunch.tools.min.js"></script>
        <script src="assets/js/jquery.themepunch.revolution.min.js"></script>

        <script src="assets/js/extensions/revolution.extension.actions.min.js"></script>
        <script src="assets/js/extensions/revolution.extension.carousel.min.js"></script>
        <script src="assets/js/extensions/revolution.extension.kenburn.min.js"></script>
        <script src="assets/js/extensions/revolution.extension.layeranimation.min.js"></script>
        <script src="assets/js/extensions/revolution.extension.migration.min.js"></script>
        <script src="assets/js/extensions/revolution.extension.navigation.min.js"></script>
        <script src="assets/js/extensions/revolution.extension.parallax.min.js"></script>
        <script src="assets/js/extensions/revolution.extension.slideanims.min.js"></script>
        <script src="assets/js/extensions/revolution.extension.video.min.js"></script>

        <script src="assets/js/jquery.nice-select.min.js"></script>

        <script src="assets/js/custom.js"></script>
    </body>

    <!-- Mirrored from templates.hibootstrap.com/jeel/default/shop.html by HTTrack Website Copier/3.x [XR&CO'2014], Tue, 12 Jan 2021 17:36:06 GMT -->
</html>
