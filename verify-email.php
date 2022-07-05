<?php
$title = "Verify Email";
include_once "layouts/header.php";
include_once "layouts/nav.php";
include_once "app/requests/RegisterRequest.php";
include_once "app/mail/SendMail.php";
include_once "app/database/models/User.php";
if($_POST){
   // required , regex , exists
    $emailValidation = new RegisterRequest;
    $emailValidation->setEmail($_POST['email']);
    $emailValidationResult = $emailValidation->emailValidation();
    if(empty($emailValidationResult)){
        $emailExistsResult = $emailValidation->emailExists();
        if(!empty($emailExistsResult)){
            // send mail
            $code = rand(10000,99999);
            // update code in db
            $userData = new User;
            $userData->setCode($code);
            $userData->setEmail($_POST['email']);
            $result = $userData->updateCode();
            if($result){
                $subject = "Forget Password";
                $body = "Your Verification Code Is:<b>$code</b><br>Thank You.";
                $newMail = new SendMail($_POST['email'],$subject,$body);
                $mailResult = $newMail->send();
                if(empty($mailResult)){
                    $_SESSION['email'] = $_POST['email'];
                    header('location:check-code.php?page=verify-email');
                }
            }
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
                                        <input type="email" name="email" placeholder="Email" value="<?php if(isset($_POST['email'])){echo $_POST['email'];} ?>">
                                        <?php 
                                            if(!empty($emailValidationResult)){
                                                foreach ($emailValidationResult as $key => $value) {
                                                    echo $value;
                                                }
                                            }
                                            if(!empty($mailResult)){
                                                foreach ($mailResult as $key => $value) {
                                                    echo $value;
                                                }
                                            }
                                            if(isset($emailExistsResult) && empty($emailExistsResult)){
                                                echo "<div class='alert alert-danger'> These Email Donsn't Exsits In Our Records </div>";
                                            }

                                            if(isset($result) && !$result){
                                                echo "<div class='alert alert-danger'> Something Went Wrong </div>";
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