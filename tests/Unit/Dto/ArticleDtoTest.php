<?php

namespace App\Tests\Unit\Dto;

use App\Dto\Article\CreateArticleDto;
use App\Dto\Interfaces\ArticleRequestInterface;
use App\Tests\Unit\Traits\ValidationTestTrait;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ArticleDtoTest extends KernelTestCase
{

    use ValidationTestTrait;
    private AbstractDatabaseTool $databaseTool;
    private ValidatorInterface $validator;

    public function setUp(): void
    {
        self::bootKernel();
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
        $this->validator = self::getContainer()->get(ValidatorInterface::class);

        $this->databaseTool->loadFixtures();
    }

    private function getArticleDto(array $data = []): ArticleRequestInterface
    {
        return new CreateArticleDto(
            $data['title'] ?? 'Article de test',
            $data['content'] ?? 'Contenu de test',
            $data['shortContent'] ?? 'ShortContent',
            $data['enabled'] ?? false,
            $data['user'] ?? 1
        );
    }

    public function testCreateArticleDtoValid(): void
    {
        $this->assertValidationErrors($this->getArticleDto(), []);
    }

    public function testCreateArticleDtoNoUniqueTitle(): void
    {
        // On charge les fixtures avant le test
        $this->databaseTool->loadAliceFixture([
            \dirname(__DIR__) . '/Fixtures/ArticleFixtures.yaml'
        ]);

        $this->assertValidationErrors(
            $this->getArticleDto([
                'title' => 'Article de test',
            ]),
            [
                'title' => 'Ce titre est déjà utilisé par un autre article',
            ]
        );
    }
}
