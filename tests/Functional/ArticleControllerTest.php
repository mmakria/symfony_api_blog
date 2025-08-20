<?php

namespace App\Tests\Functional;

use App\Entity\Article;
use App\Entity\User;
use App\Repository\UserRepository;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ArticleControllerTest extends WebTestCase
{

    //Propriété qui va stocker notre client léger (pour envoyer des requêtes)
    private KernelBrowser $client;
    private AbstractDatabaseTool $databaseTool;


    public function setUp(): void
    {
        //Création du client léger pour les tests
        $this->client = self::createClient();
        $this->databaseTool = self::getContainer()->get(DatabaseToolCollection::class)->get();
    }

    private function getUser(string $username = 'admin'): ?User
    {
        // On load les fixtures
        $this->databaseTool->loadAliceFixture([
            __DIR__ . '/Fixtures/UserFixtures.yaml'
        ]);

        // On récupère l'utilisateur par son nom d'utilisateur
        $user = self::getContainer()->get(UserRepository::class)
            ->findOneBy(['username' => $username]);

        // On le renvois
        return $user;
    }

    public function  testIndexEndpointWithNoConnectedUser(): void
    {
        $this->client->request(
            'GET',
            '/api/admin/articles'
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testIndexEndpointWithConnectedUser(): void
    {
        $this->client->loginUser($this->getUser('user'), ('login'));
        $this->client->request(
            'GET',
            '/api/admin/articles'
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testIndexEndpointWithConnectedAdmin(): void
    {
        $this->client->loginUser($this->getUser('admin'), ('login'));
        $this->client->request(
            'GET',
            '/api/admin/articles'
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testIndexEndpointValidateStructureJsonResponse(): void
    {
        $this->client->loginUser($this->getUser('admin'), ('login'));
        $this->client->request(
            'GET',
            '/api/admin/articles'
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('items', $response);
        $this->assertArrayHasKey('meta', $response);
        $this->assertArrayHasKey('pages', $response['meta']);
        $this->assertArrayHasKey('total', $response['meta']);
    }

    public function testIndexEndpointValidateNumberOfItemsDefault(): void
    {
        $this->client->loginUser($this->getUser('admin'), ('login'));

        // On charge les fixtures

        $this->databaseTool->loadAliceFixture([
            __DIR__ . '/Fixtures/ArticleFixtures.yaml'
        ]);

        $this->client->request(
            'GET',
            '/api/admin/articles'
        );

        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(6, $response['items']);
    }

    public function testIndexEndpointValidateNumberOfItemsWithLimitParameter(): void
    {

        $this->client->loginUser($this->getUser('admin'), ('login'));

        // On charge les fixtures

        $this->databaseTool->loadAliceFixture([
            __DIR__ . '/Fixtures/ArticleFixtures.yaml'
        ]);

        $this->client->request(
            'GET',
            '/api/admin/articles?limit=1'
        );


        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $response['items']);
        $this->assertEquals(12, $response['meta']['pages']);
    }

    public function testIndexEndpointValidateErrorWhenLimitIsNotPositive(): void
    {
        $this->client->loginUser($this->getUser(), ('login'));

        $this->databaseTool->loadAliceFixture([
            __DIR__ . '/Fixtures/ArticleFixtures.yaml'
        ]);

        $this->client->request(
            'GET',
            '/api/admin/articles?limit=-1',
            server: [
                'HTTP_ACCEPT' => 'application/json',
            ]
        );


        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('limit: This value should be positive.', $response['detail']);
    }

    public function testIndexEndpointValidateErrorWhenPageIsNotPositive(): void
    {
        $this->client->loginUser($this->getUser(), ('login'));
        $this->client->request(
            'GET',
            '/api/admin/articles?page=-1',
            server: [
                'HTTP_ACCEPT' => 'application/json',
            ]
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('page: This value should be positive.', $response['detail']);
    }

    public function testIndexEndpointValidateFirstItemWhenPageIsChange(): void
    {

        $this->client->loginUser($this->getUser(), ('login'));
        $this->databaseTool->loadAliceFixture([
            __DIR__ . '/Fixtures/ArticleFixtures.yaml'
        ]);
        $this->client->request(
            'GET',
            '/api/admin/articles?limit=1&page=2',
            server: [
                'HTTP_ACCEPT' => 'application/json',
            ]
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);


        $this->assertCount(1, $response['items']);
        $this->assertEquals(2, $response['items'][0]['id']);
    }



    public function testCreateEndpointWithNotConnecteUser(): void {}

    public function testCreateArticle(): void
    {

        $this->client->loginUser($this->getUser(), ('login'));

        $articleData = [
            'title' => 'Nouvel Article Test',
            'content' => 'Contenu de test complet',
            'shortContent' => 'Résumé court',
            'enabled' => true,
            'user' => 1
        ];

        $this->client->request(
            'POST',
            '/api/admin/articles/create',
            [],
            [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT'  => 'application/json',
            ],
            json_encode($articleData)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
    }
}
