<?php
session_start();

session_destroy();

echo "<script>alert('Signed Out Successfuly!');window.location='login.php';</script>";



?>

