<?php
$title = "Set New Password";
include_once "layouts/header.php";
include_once "layouts/nav.php";
include_once "app/requests/RegisterRequest.php";
include_once "app/database/models/User.php";
if($_POST){
   $passwordVaildation = new RegisterRequest;
    $passwordValidationResult = $passwordVaildation->setPassword($_POST['password'])
    ->setConfirmPassword($_POST['confirm_password'])
    ->passwordValidation();
    if(empty($passwordValidationResult)){
        $userData = new User;
        $userData->setPassword($_POST['password']);
        $userData->setEmail($_SESSION['email']);
        $result = $userData->updateUserPassword();
        if($result){
            unset($_SESSION['email']);
            header('location:login.php');die;
        }else{
            $message = "<div class='alert alert-danger'> Something Went Wrong </div>";
        }
    }
}
?>

<!-- Breadcrumb Area End -->
<div class="login-register-area ptb-100">
    <div class="container">
        <div class="row">
            <div class="col-lg-7 col-md-12 ml-auto mr-auto">
                <div class="login-register-wrapper">
                    <div class="login-register-tab-list nav">
                        <a class="active" data-toggle="tab" href="#lg1">
                            <h4> <?= $title ?> </h4>
                        </a>
                    </div>
                    <div class="tab-content">
                        <div id="lg1" class="tab-pane active">
                            <div class="login-form-container">
                                <div class="login-register-form">
                                    <form  method="post">
                                        <?php 
                                            if(isset($message)){
                                                echo $message;
                                            }
                                        ?>
                                        <input type="password" name="password" placeholder="New Password" >
                                        <?php 
                                            if(isset($passwordValidationResult['password-required'])){
                                               echo $passwordValidationResult['password-required'];
                                            }
                                            if(isset($passwordValidationResult['password-regex'])){
                                                echo $passwordValidationResult['password-regex'];
                                             }
                                        ?>
                                        <input type="password" name="confirm_password" placeholder="Confirm Password" >
                                        <?php 
                                            if(isset($passwordValidationResult['password-confirm'])){
                                               echo $passwordValidationResult['password-confirm'];
                                            }
                                            if(isset($passwordValidationResult['confirm_password-required'])){
                                                echo $passwordValidationResult['confirm_password-required'];
                                             }
                                        ?>
                                        <div class="button-box">
                                            <button type="submit"><span><?= $title ?></span></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                       
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include_once "layouts/footer.php";
?>