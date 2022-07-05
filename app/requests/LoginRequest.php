<?php

class LoginRequest
{
    private $password;



    /**
     * Get the value of password
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set the value of password
     *
     * @return  self
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }


    public function passwordValidaion()
    {
        $errors = [];
        $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,20}$/";
        // required
        if (empty($this->password)) {
            $errors['password-required'] = "<div class='alert alert-danger'> Password Is Required </div>";
        }

        if (empty($errors)) {
            if (!preg_match($pattern, $this->password)) {
                $errors['password-regex'] = "<div class='alert alert-danger'> Wrong Email Or Password </div>";
            }
        }
        return $errors;
    }

    // public function checkPasswordRegEx($message =" Wrong Email Or Password ")
    // {
    //     $errors= [];
    //     $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,20}$/";
    //     if (!preg_match($pattern, $this->password)) {
    //         $errors['password-regex'] = "<div class='alert alert-danger'> $message </div>";
    //     }
    //     return $errors;

    // }
}
