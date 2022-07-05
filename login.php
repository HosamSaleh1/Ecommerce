<?php
$title = "Login";
include_once "layouts/header.php";
include_once "app/middleware/guest.php";
include_once "layouts/nav.php";
include_once "layouts/breadcrumb.php";
include_once "app/requests/LoginRequest.php";
include_once "app/requests/RegisterRequest.php";
include_once "app/database/models/User.php";

if($_POST){
    $emailValidaiton = new RegisterRequest;
    $emailValidaiton->setEmail($_POST['email']);
    $emailValidaitonResult = $emailValidaiton->emailValidation();

    $passwordValidaiton = new LoginRequest;
    $passwordValidaitonResult = $passwordValidaiton->setPassword($_POST['password'])->passwordValidaion();

    if(empty($emailValidaitonResult) && empty($passwordValidaitonResult)){
        // search in db
        $userData = new user;
        $loginResult = $userData->setPassword($_POST['password'])->setEmail($_POST['email'])->login();
        if($loginResult){
            $user = $loginResult->fetch_object();
            // check if user verified
            if($user->email_verified_at){
                // user verifeid
                if($user->status == 1){
                    // user verifeid & active
                    // set cookie
                    if(isset($_POST['remember_me'])){
                        setcookie('user_remember',$_POST['email'],time() + (84600 * 30) , '/');
                    }
                    $_SESSION['user'] =  $user;
                    header('location:index.php');die;
                }elseif($user->status == 0) {
                    // user verified & not active
                    $message = "<div class='alert alert-warning'> Please Contact The Admin To Activate Your Acc email@email.com </div>";
                }elseif($user->status == 2){
                    // user verified & blocked
                    $message = "<div class='alert alert-danger'> Your Account Has been Blocked </div>";
                }
            }else{
                // user not verifeid
                $_SESSION['email'] = $_POST['email'];
                header('location:check-code.php?page=login');die;
            }
        }else{
            $message = "<div class='alert alert-danger'> Wrong Email Or Password </div>";
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
                                            if(!empty($emailValidaitonResult)){
                                                foreach ($emailValidaitonResult as $key => $value) {
                                                    echo $value;
                                                }
                                            }
                                        ?>
                                        <input type="password" name="password" placeholder="Password">
                                        <?php 
                                            if(!empty($passwordValidaitonResult)){
                                                foreach ($passwordValidaitonResult as $key => $value) {
                                                    echo $value;
                                                }
                                            }
                                            if(isset($message)){
                                                echo $message;
                                            }
                                        ?>
                                        
                                        <div class="button-box">
                                            
                                            <div class="login-toggle-btn">
                                            <input type="checkbox" name="remember_me" id="remember_me" >
                                                <label>Remember me</label>
                                                <a href="verify-email.php">Forgot Password?</a>
                                            </div>
                                            <button type="submit"><span>Login</span></button>
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