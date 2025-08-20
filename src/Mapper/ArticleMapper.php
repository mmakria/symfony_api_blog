<?php

namespace App\Mapper;

use App\Dto\Article\CreateArticleDto;
use App\Dto\Article\UpdateArticleDto;
use App\Entity\Article;
use App\Entity\User;
use App\Repository\UserRepository;

readonly class ArticleMapper
{
    public function __construct(private UserRepository $userRepository) {}

    public function map(CreateArticleDto|UpdateArticleDto $dto, ?Article $article = null, ?User $user = null): Article
    {
        $article ??= new Article;

        if (null !== $dto->getUser()) {
            $user = $this->userRepository->find($dto->getUser());
            $article->setUser($user);
        }

        if (null !== $dto->getTitle()) {
            $article->setTitle($dto->getTitle());
        }

        if (null !== $dto->getShortContent()) {
            $article->setShortContent(
                $dto->getShortContent()
            );
        }

        if (null !== $dto->getContent()) {
            $article->setContent(
                $dto->getContent()
            );
        }

        if (null !== $dto->getEnabled()) {
            $article->setEnabled($dto->getEnabled());
        }

        return $article;
    }
}
