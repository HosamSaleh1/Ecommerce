<?php
$title = "My Account";
include_once "layouts/header.php";
include_once "app/middleware/auth.php";
include_once "layouts/nav.php";
include_once "layouts/breadcrumb.php";
include_once "app/database/models/User.php";
include_once "app/service/UploadImage.php";
include_once "app/requests/RegisterRequest.php";
include_once "app/requests/ChangePasswordRequest.php";
include_once "app/mail/SendMail.php";
// get reqeust
$userData = new User;
$userData->setEmail($_SESSION['user']->email);

$errors = [];
$success = [];
// post request
if (isset($_POST['update-profile'])) {
    // validation all fields => must be required
    if (empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['gender']) || empty($_POST['phone'])) {
        $errors['update-profile']['required'] = "<div class='alert alert-danger'> All Fields Are Required </div>";
    }
    // if we have no validation errors
    if (empty($errors['update-profile'])) {
        // pass validated data to user object
        $userData->setFirst_name($_POST['first_name'])
            ->setLast_name($_POST['last_name'])
            ->setGender($_POST['gender'])
            ->setPhone($_POST['phone']);

        // check if image exists
        if ($_FILES['image']['error'] == 0) {
            // pass image to UploadImage Class
            $uploadImage = new UploadImage($_FILES['image']);
            // validate on size & validate on extension & upload
            $uploadErrors = $uploadImage->validateOnSize()->validateOnExtension()->upload("assets/img/users/");
            // upload Errors empty -> no errors
            if (empty($uploadErrors)) {
                // pass image to user object
                $userData->setImage($uploadImage->getPhotoName());
                $_SESSION['user']->image = $uploadImage->getPhotoName();
            }
        }
        // update user
        $result = $userData->update();
        if ($result) {
            $_SESSION['user']->first_name = $_POST['first_name'];
            $_SESSION['user']->last_name = $_POST['last_name'];
            $_SESSION['user']->gender = $_POST['gender'];
            $_SESSION['user']->phone = $_POST['phone'];
            $success['update-profile']['success'] = "<div class='alert alert-success'> data updated </div>";
        } else {
            $errors['update-profile']['something'] = "<div class='alert alert-danger'> something went wrong </div>";
        }
    }
}
if (isset($_POST['update-password'])) {
    $passwordValidation = new ChangePasswordRequest;
    // old password = required
    // if(empty($_POST['old_password'])){
    //     $errors['update-password']['old']['required'] = "<div class='alert alert-danger'> Old Password Is Required </div>";
    // }
    $passwordValidation->setPassword($_POST['old_password']);
    $passwordValidation->passwordRequired() ? $errors['update-password']['old']['required'] = $passwordValidation->passwordRequired() : "";
    // new password = required
    // if(empty($_POST['new_password'])){
    //     $errors['new']['required'] = "<div class='alert alert-danger'> New Password Is Required </div>";
    // }
    $passwordValidation->setPassword($_POST['new_password']);
    $passwordValidation->passwordRequired() ? $errors['update-password']['new']['required'] = $passwordValidation->passwordRequired() : "";

    // confrim password = requried
    // if(empty($_POST['confirm_password'])){
    //     $errors['confrim']['required'] = "<div class='alert alert-danger'> Confirm Password Is Required </div>";
    // }
    $passwordValidation->setPassword($_POST['confirm_password']);
    $passwordValidation->passwordRequired() ? $errors['update-password']['confirm']['required'] = $passwordValidation->passwordRequired() : "";


    if (empty($errors)) {
         // old password => correct
        // if($_SESSION['user']->password != $_POST['old_password']){
        //     $errors['update-password']['old']['wrong'] = "<div class='alert alert-danger'> Old Password Is Wrong </div>";
        // }
        $passwordValidation->setPassword(sha1($_POST['old_password']));
        $passwordValidation->setComparedPassword($_SESSION['user']->password);
        $passwordValidation->passwordCompare("Old Password Is Wrong", "") ? $errors['update-password']['old']['wrong'] = $passwordValidation->passwordCompare("Old Password Is Wrong", "") : "";
        if (empty($errors)) {
            // if($_POST['new_password'] != $_POST['confirm_password']){
            //     $errors['confirm']['not-matched'] = "<div class='alert alert-danger'> Password Not Confirmed </div>";
            // }
            $passwordValidation->setPassword($_POST['confirm_password']);
            $passwordValidation->setComparedPassword($_POST['new_password']);
            $passwordValidation->passwordCompare("Password Not Confirmed", "") ? $errors['update-password']['confirm']['not-matched'] = $passwordValidation->passwordCompare("Password Not Confirmed", "") : "";
            if(empty($errors)){
                // if(!preg_match($pattern,$_POST['new_password'])){
                //         $errors['new']['regex'] = "<div class='alert alert-danger'>  Minimum eight and maximum 20 characters, at least one uppercase letter, one lowercase letter, one number and one special character: </div>";
                //  }
                $passwordValidation->setPassword($_POST['new_password']);
                $passwordValidation->passwordRegEx() ? $errors['update-password']['new']['regex'] = $passwordValidation->passwordRegEx("Minimum eight and maximum 20 characters, at least one uppercase letter, one lowercase letter, one number and one special character:") : "";
                if(empty($errors)){
                    // if($_POST['new_password'] == $_POST['old_password']){
                    //     $errors['new']['old'] = "<div class='alert alert-danger'> New Password Must Be Not Equal To Old Password </div>";
                    // }
                    $passwordValidation->setPassword($_POST['old_password']);
                    $passwordValidation->passwordCompare("", "New Password Must Be Not Equal To Old Password") ? $errors['update-password']['new']['old'] = $passwordValidation->passwordCompare("", "New Password Must Be Not Equal To Old Password") : "";
                }
            }
            
        }
    }

    // update password to this user
    $result = $userData->setPassword($_POST['new_password'])->updateUserPassword();
    if ($result) {
        $_SESSION['user']->password = sha1($_POST['new_password']);
        $success['update-password']['database']['success'] = "<div class='alert alert-success'> data updated </div>";
    } else {
        $errors['update-password']['database']['something'] = "<div class='alert alert-danger'> something went wrong </div>";
    }

}
if(isset($_POST['update-email'])){
    // required
    // regex
    $emailValidation = new RegisterRequest;
    $emailValidation->setEmail($_POST['email']);
    $errors['update-email'] = $emailValidation->emailValidation();
    // new email
    if($_POST['email'] == $_SESSION['user']->email){
        $errors['update-email']['old-email'] = "<div class='alert alert-danger'> You Should Change Your Email Address </div>";
    }else{
         // dosen't exists in db
        $userData->setEmail($_POST['email']);
        $userEmail =  $userData->checkIfEmailExists();
        if($userEmail){
            $errors['update-email']['email-exists'] = "<div class='alert alert-danger'> Email Already Exists </div>";
        }
    }
   if(empty($errors['update-email'])){
        // update email in db
        $userData->setId($_SESSION['user']->id);
        $code = rand(10000,99999);
        $userData->setCode($code);
        $userData->setEmail_verified_at('NULL');
        $userData->setStatus(0);
        $result = $userData->updateUserEmail();
        if ($result) {
            $success['update-email']['success'] = "<div class='alert alert-success'> We Have Sent A fresh Email To You , You Will Redirect To Verify Your Code in 5 seconds </div>";
            $subject = "Change Email";
            $body = "Your Verification Code Is:<b>$code</b><br>Thank You.";
            // $body = "Please Click On this button To Verify Your Account <br> <a href='http://localhost/nti/p12/ecommerce/verify.php?email={$_POST['email']}'> Verify Account </a>";
            $newMail = new SendMail($_POST['email'],$subject,$body);
            $mailResult = $newMail->send();
            if(empty($mailResult)){
                $_SESSION['email'] = $_POST['email'];
                $_SESSION['user']->email = $_POST['email'];
                header('Refresh:5 , URL=check-code.php?page=my-account');
            }
        } else {
            $errors['update-email']['something'] = "<div class='alert alert-danger'> something went wrong </div>";
        }
   }
}
$userData->setEmail($_SESSION['user']->email);
$user = $userData->checkIfEmailExists()->fetch_object();



?>
<!-- my account start -->
<div class="checkout-area pb-80 pt-100">
    <div class="container">
        <div class="row">
            <div class="ml-auto mr-auto col-lg-9">
                <div class="checkout-wrapper">
                    <div id="faq" class="panel-group">
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h5 class="panel-title"><span>1</span> <a data-toggle="collapse" data-parent="#faq" href="#my-account-1">Edit your account information </a></h5>
                            </div>
                            <div id="my-account-1" class="panel-collapse collapse <?php if (isset($_POST['update-profile'])) {
                                                                                        echo "show";
                                                                                    } ?>">
                                <!-- show -->
                                <div class="panel-body">
                                    <div class="billing-information-wrapper">
                                        <div class="account-info-wrapper">
                                            <h4>My Account Information</h4>
                                            <h5>Your Personal Details</h5>
                                        </div>
                                        <form action="" method="post" enctype="multipart/form-data">

                                            <div class="row">
                                                <div class="col-12">
                                                    <?php
                                                    if (!empty($errors['update-profile'])) {
                                                        foreach ($errors['update-profile'] as $key => $value) {
                                                            echo $value;
                                                        }
                                                    }
                                                    if (!empty($success['update-profile'])) {
                                                        foreach ($success['update-profile'] as $key => $value) {
                                                            echo $value;
                                                        }
                                                    }
                                                    if (!empty($uploadErrors)) {
                                                        foreach ($uploadErrors as $key => $value) {
                                                            echo $value;
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <div class="col-4 offset-4 mb-5">
                                                    <img src="assets/img/users/<?= $user->image ?>" alt="" class="rounded-circle w-100">
                                                    <input type="file" name="image" id="" class="form-control">
                                                </div>
                                                <div class="col-lg-6 col-md-6">
                                                    <div class="billing-info">
                                                        <label>First Name</label>
                                                        <input type="text" name="first_name" value="<?= $user->first_name ?>">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6">
                                                    <div class="billing-info">
                                                        <label>Last Name</label>
                                                        <input type="text" name="last_name" value="<?= $user->last_name ?>">
                                                    </div>
                                                </div>

                                                <div class="col-lg-6 col-md-6">
                                                    <div class="billing-info">
                                                        <label>Mobile</label>
                                                        <input type="text" name="phone" value="<?= $user->phone ?>">
                                                    </div>
                                                </div>
                                                <div class="col-lg-6 col-md-6">
                                                    <div class="billing-info">
                                                        <label>Gender</label>
                                                        <select name="gender" class="form-control" id="">
                                                            <option <?= ($user->gender == 'm') ? "selected" :  '' ?> value="m">Male</option>
                                                            <option <?= ($user->gender == 'f') ? "selected" :  '' ?> value="f">Female</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="billing-back-btn">

                                                <div class="billing-btn">
                                                    <button type="submit" name="update-profile">Update Profile</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h5 class="panel-title"><span>2</span> <a data-toggle="collapse" data-parent="#faq" href="#my-account-2">Change your password </a></h5>
                            </div>
                            <div id="my-account-2" class="panel-collapse collapse <?php if (isset($_POST['update-password'])) {
                                                                                        echo "show";
                                                                                    } ?>">
                                <div class="panel-body">
                                    <div class="billing-information-wrapper">
                                        <div class="account-info-wrapper">
                                            <h4>Change Password</h4>
                                            <h5>Your Password</h5>
                                        </div>
                                        <form action="" method="post">
                                            <div class="row">
                                                <div class="col-12">
                                                    <?php
                                                    if (!empty($errors['update-password']['database'])) {
                                                        foreach ($errors['update-password']['database'] as $key => $value) {
                                                            echo $value;
                                                        }
                                                    }
                                                    if (!empty($success['update-password']['database'])) {
                                                        foreach ($success['update-password']['database'] as $key => $value) {
                                                            echo $value;
                                                        }
                                                    }
                                                    
                                                    ?>
                                                </div>
                                                <div class="col-lg-12 col-md-12">
                                                    <div class="billing-info">
                                                        <label>Old Password</label>
                                                        <input type="password" name="old_password">
                                                    </div>
                                                    <?php
                                                    if (!empty($errors['update-password']['old'])) {
                                                        foreach ($errors['update-password']['old'] as $key => $value) {
                                                            if (!empty($value))
                                                                echo "<div class='alert alert-danger'>$value</div>";
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <div class="col-lg-12 col-md-12">
                                                    <div class="billing-info">
                                                        <label>New Password</label>
                                                        <input type="password" name="new_password">
                                                    </div>
                                                    <?php
                                                    if (!empty($errors['update-password']['new'])) {
                                                        foreach ($errors['update-password']['new'] as $key => $value) {
                                                            if (!empty($value))
                                                                echo "<div class='alert alert-danger'>$value</div>";
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                                <div class="col-lg-12 col-md-12">
                                                    <div class="billing-info">
                                                        <label>Password Confirm</label>
                                                        <input type="password" name="confirm_password">
                                                    </div>
                                                    <?php
                                                    if (!empty($errors['update-password']['confirm'])) {
                                                        foreach ($errors['update-password']['confirm'] as $key => $value) {
                                                            if (!empty($value))
                                                                echo "<div class='alert alert-danger'>$value</div>";
                                                        }
                                                    }
                                                    ?>
                                                </div>

                                            </div>
                                            <div class="billing-back-btn">
                                                <div class="billing-btn">
                                                    <button type="submit" name="update-password">Update Password</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                <h5 class="panel-title"><span>3</span> <a data-toggle="collapse" data-parent="#faq" href="#my-account-3">Change Your Email </a></h5>
                            </div>
                            <div id="my-account-3" class="panel-collapse collapse <?php if(isset($_POST['update-email'])){echo 'show';} ?>">
                                <div class="panel-body">
                                    <div class="billing-information-wrapper">
                                        <div class="account-info-wrapper">
                                            <h4>Your Email Address</h4>
                                        </div>
                                        <form action="" method="post">
                                            <div class="row">
                                                <div class="col-12">
                                                <?php 
                                                            if(!empty($success['update-email'])){
                                                                foreach ($success['update-email'] as $key => $value) {
                                                                    echo $value;
                                                                }
                                                            }
                                                        ?>
                                                </div>
                                                <div class="col-lg-12 col-md-12">
                                                    <div class="billing-info">
                                                        <label>Email</label>
                                                        <input type="email" name="email" value="<?= $user->email; ?>">
                                                        <?php 
                                                            if(!empty($errors['update-email'])){
                                                                foreach ($errors['update-email'] as $key => $value) {
                                                                    echo $value;
                                                                }
                                                            }
                                                        ?>
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="billing-back-btn">
                                                <div class="billing-btn">
                                                    <button type="submit" name="update-email">Update Email</button>
                                                </div>
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
</div>
<!-- my account end -->
<?php
include_once "layouts/footer.php";
?>