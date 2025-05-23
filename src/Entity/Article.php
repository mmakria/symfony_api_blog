<?php

namespace App\Entity;

use App\Entity\Traits\DateTimeTrait;
use App\Repository\ArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQUE_ARTICLE_TITLE', fields: ['title'])]
#[ORM\HasLifecycleCallbacks]
class Article
{
    use DateTimeTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(groups: ['articles:admin:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(groups: ['articles:admin:read', 'articles:admin:write'])]
    private ?string $title = null;

    #[Gedmo\Slug(fields: ['title'] )]
    #[ORM\Column(length: 255)]
    #[Groups(groups: ['articles:admin:read', 'articles:admin:write'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(groups: ['articles:admin:read', 'articles:admin:write'])]
    private ?string $content = null;

    #[ORM\Column(length: 255)]
    #[Groups(groups: ['articles:admin:read', 'articles:admin:write'])]
    private ?string $shortContent = null;

    #[ORM\Column]
    #[Groups(groups: ['articles:admin:read', 'articles:admin:write'])]
    private ?bool $enabled = null;

    #[ORM\ManyToOne(inversedBy: 'articles')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(groups: ['articles:admin:read'])]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getShortContent(): ?string
    {
        return $this->shortContent;
    }

    public function setShortContent(string $shortContent): static
    {
        $this->shortContent = $shortContent;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
