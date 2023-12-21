<?php

namespace App\Dto\Request;

use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ConfirmationEmailInputDto
{
    #[Assert\NotBlank]
    #[Assert\Email]
    private $username;

    #[Assert\NotBlank]
    private $code;

    #[Assert\NotBlank]
    private $password;

    #[Assert\NotBlank]
    private $plainPassword;

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

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code): void
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password): void
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    /**
     * @param mixed $plainPassword
     */
    public function setPlainPassword($plainPassword): void
    {
        $this->plainPassword = $plainPassword;
    }

    /**
     * @Assert\Callback
     */
    public function validatePasswordEquality(ExecutionContextInterface $context, $payload)
    {
        // Your custom validation logic
        if ($this->password !== $this->plainPassword) {
            $context->buildViolation('The password and plainPassword should match.')
                ->atPath('plainPassword')
                ->addViolation();
        }
    }
}