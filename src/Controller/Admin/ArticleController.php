<?php

namespace App\Controller\Admin;

use App\Dto\Article\CreateArticleDto;
use App\Dto\Article\UpdateArticleDto;
use App\Dto\Filter\ArticleFilterDto;
use App\Mapper\ArticleMapper;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/articles', name: 'api_admin_users')]
class ArticleController extends AbstractController
{
    public function __construct(
        private readonly ArticleRepository      $articleRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ArticleMapper          $articleMapper,
    )
    {}

    #[Route('', name: 'read_articles', methods: ['GET'])]
    public function listArticles(
        #[MapQueryString]
        ArticleFilterDto $filterDto,
    ): JsonResponse
    {

        return $this->json($this->articleRepository->findPaginate($filterDto),
            Response::HTTP_OK,
            context: ['groups' => ['articles:admin:read']]);
    }

    #[Route('/{id}', name: 'read_one_article', methods: ['GET'])]
    public function readArticle(Article $article): JsonResponse
    {
        return $this->json(
            $article,
            Response::HTTP_OK,
            context: ['groups' => ['articles:admin:read']]);
    }

    #[Route('/{id}', name: 'delete_one_article', methods: ['DELETE'])]
    public function deleteOneArticle(Article $article): JsonResponse
    {
        $this->entityManager->remove($article);
        $this->entityManager->flush();
        return $this->json(
            null,
            Response::HTTP_NO_CONTENT,
        );
    }

    #[Route('/create', name: 'create_article', methods: ['POST'])]
    public function createArticle(
        #[MapRequestPayload]
        CreateArticleDto $dto
    ): JsonResponse
    {
        $user = $this->getUser();
        $article = $this->articleMapper->map($dto, null, $user);
        $this->entityManager->persist($article);
        $this->entityManager->flush();
        return $this->json(
            [
                'id' => $article->getId()
            ],
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'update_article', methods: ['PATCH'])]
    public function updateArticle(
        Article $article,
        #[MapRequestPayload]
        UpdateArticleDto $dto
    ): JsonResponse
    {
        $user = $this->getUser();
        $article = $this->articleMapper->map($dto, $article, $user);
        $this->entityManager->persist($article);
        $this->entityManager->flush();
        return $this->json(
            [
                'message' => 'Article ' .  $article->getId() . ' mis à jour'
            ],
            Response::HTTP_OK,
            context: ['groups' => ['articles:admin:write']]
        );
    }
}