<?php

namespace App\Dto\Article;

use App\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateArticleDto
{
    public function __construct(
        #[Assert\Length(
            min: 6,
            max: 255,
            minMessage: "Le Titre est trop court.",
            maxMessage: "Le Titre est trop long.",
        )]
        private ?string $title = null,
        #[Assert\Length(
            min: 6,
            max: 255,
            minMessage: "Le short content est trop court.",
            maxMessage: "Le short content est trop long.",
        )]
        private ?string $shortContent = null,
        #[Assert\Length(
            min: 6,
            max: 255,
            minMessage: "Le content est trop court.",
            maxMessage: "Le content est trop long.",
        )]
        private ?string $content = null,
        private ?bool $enabled = true,
        private ?User $user = null,
    ) {
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function getShortContent(): ?string
    {
        return $this->shortContent;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
