<?php 

session_start();

unset($_SESSION['user']);

setcookie('user_remember',"", time() - 1 , '/');

header('location:login.php');die;