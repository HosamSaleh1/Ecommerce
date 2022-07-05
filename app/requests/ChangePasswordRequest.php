<?php 

class ChangePasswordRequest {
    private $password;
    private $comparedPassword;
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

    /**
     * Get the value of comparedPassword
     */ 
    public function getComparedPassword()
    {
        return $this->comparedPassword;
    }

    /**
     * Set the value of comparedPassword
     *
     * @return  self
     */ 
    public function setComparedPassword($comparedPassword)
    {
        $this->comparedPassword = $comparedPassword;

        return $this;
    }

    public function passwordRequired()
    {
        $errors = "";
        if(empty($this->password)){
            $errors = "This Field Is Required";
        }
        return $errors;
    }
    public function passwordCompare( string $notEqualMessage ,string $equalMessage)
    {
        $errors = $notEqualMessage;
        if($this->password == $this->comparedPassword){
            $errors = $equalMessage;
        }
        return $errors;
    }

    public function passwordRegEx( string $message = "Wrong Password Format" ,  string $pattern = "/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,20}$/")
    {
        $errors = "";
        if(!preg_match($pattern,$this->password)){
            $errors = $message;
        }
        return $errors;
    }

}