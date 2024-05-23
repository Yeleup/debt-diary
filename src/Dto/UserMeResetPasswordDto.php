<?php

namespace App\Dto;

use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

final class UserMeResetPasswordDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 6, max: 16)]
    #[Groups(['user.write'])]
    public ?string $password = null;
}