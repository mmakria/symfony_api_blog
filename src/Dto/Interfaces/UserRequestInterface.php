<?php

namespace App\Dto\Interfaces;

interface UserRequestInterface
{
    public function getPlainPassword(): ?string;

    public function getUsername(): ?string;

    public function getFirstName(): ?string;

    public function getLastName(): ?string;
}
