<?php


class UploadImage
{
    // image
    // uploaded path
    // validate or not
    // i/p => image , o/p => [] || [error]
    private $image;
    private $errors = [];
    private $photoExtension;
    private $photoName;



    public function __construct($image)
    {
        $this->image = $image;
    }


    /**
     * Get the value of photoName
     */ 
    public function getPhotoName()
    {
        return $this->photoName;
    }

    /**
     * Set the value of photoName
     *
     * @return  self
     */ 
    public function setPhotoName($photoName)
    {
        $this->photoName = $photoName;

        return $this;
    }

    public function validateOnExtension(array $allowedExtensions = ['png', 'jpg', 'jpeg'])
    {
        
        $this->photoExtension = pathinfo($this->image['name'], PATHINFO_EXTENSION); // png
        if (!in_array($this->photoExtension, $allowedExtensions)) {
            $this->errors['ext'] = "<div class='alert alert-danger'> Allowed Extensions are " . implode(",", $allowedExtensions) . " </div>";
        }
        return $this;
    }

    public function validateOnSize(int $maxUploadSize = 10 ** 6)
    {
        
        $maxUploadSizeMega = $maxUploadSize / 1000000; // 1 mega
        if ($this->image['size'] > $maxUploadSize) {
            $this->errors['size'] = "<div class='alert alert-danger'> Size Must Be Less Than $maxUploadSizeMega Mega </div>";
        }
        return $this;
    }


    public function upload(string $dir)
    {

        if (empty($this->errors)) {
            $this->photoName = time() . '.' . $this->photoExtension; //16533513524.png
            $photoPath = $dir . $this->photoName; // images/users/1653351352.png
            move_uploaded_file($this->image['tmp_name'], $photoPath);
        }
        return $this->errors;
    }

    
}
