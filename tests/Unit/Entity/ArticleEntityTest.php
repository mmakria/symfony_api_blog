<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Constraints\Week;

class ArticleEntityTest extends KernelTestCase
{



    private EntityManagerInterface $entityManager;
    private AbstractDatabaseTool $databaseTool;

    // setUp c'est un peu comme le construct
    public function setUp(): void
    {
        // Init le kernel Symfony
        self::bootKernel();
        //On récupère l'em qu'on stock dans la propriété
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
        $this->databaseTool->loadFixtures();
    }

    private function getUser(): User
    {
        return (new User)
            ->setUsername("zazazae" . uniqid())
            ->setPassword('123456');
    }

    private function getArticle(): Article
    {
        return (new Article)
            ->setTitle("Title de testez")
            ->setContent('Contenu de l article de teste')
            ->setShortContent('ShortContente')
            ->setEnabled(true)
            ->setUser($this->getUser());
    }

    private function persistData(Article $article, User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->persist($article);

        $this->entityManager->flush();
    }

    public function testGenerationSlugByTitle(): void
    {

        $user = $this->getUser();
        $article = $this->getArticle();

        $this->persistData($article, $article->getUser());
        $this->assertEquals('title-de-testez', $article->getSlug());
    }

    public function testGenerationCreatedAtOnPersist(): void
    {
        $article = $this->getArticle();
        $this->persistData($article, $article->getUser());
        $expected = (new \DateTimeImmutable())->format('Y-m-d H:i');
        $this->assertEquals($expected, $article->getCreatedAt()->format('Y-m-d H:i'));
    }

    public function testGenerationCreatedAtOnPersistWithExistingCreatedAt(): void
    {
        $createdAt = new \DateTimeImmutable('2025-01-01 12:00');
        $article = $this->getArticle()->setCreatedAt($createdAt);

        $this->persistData($article, $article->getUser());
        $this->assertEquals(
            $createdAt->format('Y-m-d H:i'),
            $article->getCreatedAt()->format('Y-m-d H:i')
        );
    }

    public function testGenerationUpdatedAtOnUpadte(): void
    {
        $article = $this->getArticle();
        $this->persistData($article, $article->getUser());

        $this->assertNull($article->getUpdatedAt());

        $article->setTitle('nouveau titre');
        $this->entityManager->flush();

        $expected = (new \DateTimeImmutable())->format('Y-m-d H:i');
        $this->assertEquals($expected, $article->getUpdatedAt()->format('Y-m-d H:i'));
    }

    public function testExceptionWhenNoUniqueTitle(): void
    {

        // Alice pour les fixtures en yaml
        $this->databaseTool->loadAliceFixture(
            [\dirname(__DIR__) . '/Fixtures/ArticleFixtures.yaml']

        );
        $article = $this->getArticle()
            ->setTitle('Article de test');

        $this->expectException(UniqueConstraintViolationException::class);

        $this->persistData($article, $article->getUser());
    }
}
