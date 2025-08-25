<?php

namespace App\Dto\User;

use App\Dto\Interfaces\UserRequestInterface;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[UniqueEntity(
    fields: ['username'],
    entityClass: User::class,
)]
readonly class RegisterUserDto implements UserRequestInterface
{
    public function __construct(
        #[Assert\NotBlank(message: "Le nom du user est requis.")]
        #[Assert\Length(
            min: 3,
            max: 180,
            minMessage: "Le nom du user est trop court.",
            maxMessage: "Le nom du user est trop long."
        )]
        private ?string $username = null,
        #[Assert\Length(
            max: 255,
            maxMessage: "Le prénom est trop long.",
        )]
        private ?string $firstName = null,
        #[Assert\Length(
            max: 255,
            maxMessage: "Le nom du user est trop long.",
        )]
        private ?string $lastName = null,
        #[Assert\NotBlank()]
        #[Assert\Length(
            min: 6,
            max: 4096,
            minMessage: "Le mot de passe est trop court.",
            maxMessage: "Le mot de passe est trop long.",
        )]
        private ?string $plainPassword = null,
        #[Assert\NotBlank()]
        #[Assert\EqualTo(
            propertyPath: 'plainPassword',
            message: 'La confirmation du mot de passe est incorrect.',
        )]
        private ?string $confirmPassword = null,
    ) {

    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function getConfirmPassword(): ?string
    {
        return $this->confirmPassword;
    }
}
