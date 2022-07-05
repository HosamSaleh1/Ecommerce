<?php
$title = "Check Code";
include_once "app/database/models/User.php";
// validation on email
$userObject = new User;
if($_GET){
    if(isset($_GET['email'])){
        $userObject->setEmail($_GET['email']);
        $userResult = $userObject->checkIfEmailExists();
        if($userResult){
            $user = $userResult->fetch_object();
        }else{
            header('location:errors/404.php');
        }
    }else{
        header('location:errors/404.php');
    }
}else{
    header('location:errors/404.php');
}

$userObject->setStatus(1);
date_default_timezone_set('Africa/Cairo');
$userObject->setEmail_verified_at(date('Y-m-d H:i:s'));
$result = $userObject->updateUserStatus();
if ($result) {
    header('location:my-account.php');
    die;
}
