<?php

namespace App\Dto\Request;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class SendConfirmationCodeInputDto
{
    #[Assert\NotBlank]
    #[Assert\Email]
    private $username;

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $email
     */
    public function setUsername($username): void
    {
        $this->username = $username;
    }
}