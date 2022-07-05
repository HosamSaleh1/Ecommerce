<?php
$title = "Check Code";
include_once "layouts/header.php";
include_once "layouts/nav.php";
include_once "layouts/breadcrumb.php";
include_once "app/requests/CheckCodeRequest.php";
include_once "app/database/models/User.php";
$availblePages = ['login','register','verify-email','my-account'];
if($_GET){
    if(isset($_GET['page'])){
        if(!in_array($_GET['page'],$availblePages)){
            header('location:errors/404.php');
        }
    }else{
        header('location:errors/404.php');
    }
}else{
    header('location:errors/404.php');
}


if($_POST){
    $validation = new CheckCodeReqeust;
    $validation->setCode($_POST['code']);
    $codeValidationResult = $validation->codeValidation();

    if(empty($codeValidationResult)){
        $userObject = new User;
        $userObject->setCode($_POST['code']);
        $userObject->setEmail($_SESSION['email']);
        $userResult = $userObject->checkIfCodeExists();
        if(!empty($userResult)){
            $user = $userResult->fetch_object();
            $userObject->setStatus(1);
            date_default_timezone_set('Africa/Cairo');
            $userObject->setEmail_verified_at(date('Y-m-d H:i:s'));
            $result = $userObject->updateUserStatus();
            if($result){
                switch ($_GET['page']) {
                    case 'register':
                        $message = "<div class='alert alert-success'> Correct Code , You Will redirect To Login Page in 3 seconds </div>";
                        unset($_SESSION['email']);
                        header('Refresh: 3; URL=login.php');
                        break;
                    case 'login':
                        $message = "<div class='alert alert-success'> Correct Code , You Will redirect To Home Page in 3 seconds </div>";
                        $_SESSION['user'] =  $user;
                        unset($_SESSION['email']);
                        header('Refresh: 3; URL=index.php');
                        break;
                    case 'my-account':
                        $message = "<div class='alert alert-success'> Correct Code , You Will redirect To My Account Page in 3 seconds </div>";
                        unset($_SESSION['email']);
                        header('Refresh: 3; URL=my-account.php');
                        break;
                    case 'verify-email':
                        $message = "<div class='alert alert-success'> Correct Code , You Will redirect To Set New Password Page in 3 seconds </div>"; 
                        header('Refresh: 3; URL=set-new-password.php');
                        break;
                    default:
                        header('location:errors/404.php');die;

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
                                        <?php 
                                            if(isset($message)){
                                                echo $message;
                                            }
                                        ?>
                                        <input type="number" name="code" placeholder="Code">
                                        <?php 
                                            if(!empty($codeValidationResult)){
                                                foreach ($codeValidationResult as $key => $value) {
                                                    echo $value;
                                                }
                                            }
                                            if(isset($userResult) AND empty($userResult)){
                                                echo "<div class='alert alert-danger'> Wrong Code </div>";
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