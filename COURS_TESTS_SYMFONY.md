# 📚 COURS COMPLET : TESTS DANS SYMFONY

## Table des matières
1. [Introduction aux Tests](#1-introduction-aux-tests)
2. [Configuration PHPUnit dans Symfony](#2-configuration-phpunit-dans-symfony)
3. [Tests Unitaires](#3-tests-unitaires)
4. [Fixtures et Données de Test](#4-fixtures-et-données-de-test)
5. [Tests d'Intégration](#5-tests-dintégration)
6. [Tests Fonctionnels](#6-tests-fonctionnels)
7. [Couverture de Code](#7-couverture-de-code)
8. [Bonnes Pratiques](#8-bonnes-pratiques)
9. [Exercices Pratiques](#9-exercices-pratiques)

---

## 1. Introduction aux Tests

### 🎯 Pourquoi tester ?

Les tests automatisés sont essentiels pour :
- **Fiabilité** : Détecter les bugs avant la production
- **Refactoring** : Modifier le code en toute confiance
- **Documentation** : Les tests documentent le comportement attendu
- **Collaboration** : Faciliter le travail en équipe
- **CI/CD** : Automatiser le déploiement

### 📊 Pyramide des Tests

```
        /\
       /E2E\      <- Tests End-to-End (peu nombreux, lents)
      /------\
     /Functional\  <- Tests Fonctionnels (moyens)
    /------------\
   /   Unit Tests  \ <- Tests Unitaires (nombreux, rapides)
  /------------------\
```

### 🔍 Types de Tests dans Symfony

1. **Tests Unitaires** : Testent une classe/méthode isolément
2. **Tests d'Intégration** : Testent plusieurs composants ensemble
3. **Tests Fonctionnels** : Testent les contrôleurs et requêtes HTTP
4. **Tests E2E** : Testent l'application complète avec navigateur

---

## 2. Configuration PHPUnit dans Symfony

### 📦 Installation

```bash
# Installation via Composer
composer require --dev phpunit/phpunit symfony/test-pack

# Bundles utiles pour les tests
composer require --dev liip/test-fixtures-bundle
composer require --dev doctrine/doctrine-fixtures-bundle
composer require --dev fakerphp/faker
```

### ⚙️ Fichier phpunit.dist.xml

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="vendor/phpunit/phpunit/phpunit.xsd"
         colors="true"
         failOnDeprecation="true"
         failOnNotice="true"
         failOnWarning="true"
         bootstrap="tests/bootstrap.php"
         cacheDirectory=".phpunit.cache"
>
    <php>
        <!-- Configuration de l'environnement -->
        <ini name="display_errors" value="1" />
        <ini name="error_reporting" value="-1" />
        <server name="APP_ENV" value="test" force="true" />
        <server name="SHELL_VERBOSITY" value="-1" />
    </php>

    <!-- Définition des suites de tests -->
    <testsuites>
        <testsuite name="unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="functional">
            <directory>tests/Functional</directory>
        </testsuite>
        <testsuite name="e2e">
            <directory>tests/EndToEnd</directory>
        </testsuite>
    </testsuites>

    <!-- Code source à analyser pour la couverture -->
    <source>
        <include>
            <directory>src</directory>
        </include>
    </source>
</phpunit>
```

### 🔧 Bootstrap des Tests

```php
// tests/bootstrap.php
<?php

use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__).'/vendor/autoload.php';

// Charge les variables d'environnement
if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

// Active le mode debug en test
if ($_SERVER['APP_DEBUG']) {
    umask(0000);
}
```

### 🗄️ Configuration Base de Données Test

```bash
# .env.test
KERNEL_CLASS='App\Kernel'
APP_SECRET='$ecretf0rt3st'
DATABASE_URL='sqlite:///%kernel.cache_dir%/test.db'
```

---

## 3. Tests Unitaires

### 📝 Structure d'un Test Unitaire

```php
<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Article;
use PHPUnit\Framework\TestCase;

class ArticleTest extends TestCase
{
    // setUp() : Exécuté avant chaque test
    protected function setUp(): void
    {
        parent::setUp();
        // Initialisation
    }

    // tearDown() : Exécuté après chaque test
    protected function tearDown(): void
    {
        // Nettoyage
        parent::tearDown();
    }

    // Les méthodes de test commencent par "test"
    public function testSlugGeneration(): void
    {
        // Arrange (Préparation)
        $article = new Article();
        $article->setTitle('Mon Super Article');

        // Act (Action)
        $slug = $article->getSlug();

        // Assert (Vérification)
        $this->assertEquals('mon-super-article', $slug);
    }
}
```

### 🎨 Test avec KernelTestCase (Accès au Container)

```php
<?php

namespace App\Tests\Unit\Dto;

use App\Dto\Article\CreateArticleDto;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ArticleDtoTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        // Boot le kernel Symfony
        self::bootKernel();
        
        // Récupère des services du container
        $this->validator = self::getContainer()->get(ValidatorInterface::class);
    }

    public function testValidation(): void
    {
        $dto = new CreateArticleDto(
            title: '',  // Titre vide pour déclencher l'erreur
            content: 'Contenu',
            shortContent: 'Court',
            enabled: true,
            user: 1
        );

        $errors = $this->validator->validate($dto);
        
        $this->assertCount(1, $errors);
        $this->assertEquals('title', $errors[0]->getPropertyPath());
    }
}
```

### 🔄 Tests avec DataProvider

```php
<?php

namespace App\Tests\Unit\Dto;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ArticleDtoTest extends TestCase
{
    /**
     * @dataProvider provideTitleValidationData
     */
    #[DataProvider('provideTitleValidationData')]
    public function testTitleValidation(string $title, bool $isValid): void
    {
        $dto = new CreateArticleDto($title, '...', '...', true, 1);
        $errors = $this->validator->validate($dto);
        
        if ($isValid) {
            $this->assertCount(0, $errors);
        } else {
            $this->assertGreaterThan(0, count($errors));
        }
    }

    public static function provideTitleValidationData(): array
    {
        return [
            'titre vide' => ['', false],
            'titre trop court' => ['a', false],
            'titre valide' => ['Mon article', true],
            'titre trop long' => [str_repeat('a', 256), false],
        ];
    }
}
```

### 🛠️ Création d'un Trait de Test Personnalisé

```php
<?php

namespace App\Tests\Unit\Traits;

use Symfony\Component\Validator\ConstraintViolationListInterface;

trait ValidationTestTrait
{
    // Codes d'erreur Symfony courants
    public const NOT_BLANK_ERROR_CODE = 'c1051bb4-d103-4f74-8988-acbcafc7fdc3';
    public const MIN_LENGTH_ERROR_CODE = '9ff3fdc4-b214-49db-8718-39c315e33d45';
    public const MAX_LENGTH_ERROR_CODE = 'd94b19cc-114f-4f44-9cc4-4138e80a87b9';
    public const UNIQUE_ERROR_CODE = '23bd9dbf-6b9b-41cd-a99e-4844bcf3077f';

    /**
     * Helper pour tester les erreurs de validation
     */
    protected function assertValidationErrors(
        object $object, 
        array $expectedErrors
    ): void {
        $errors = $this->validator->validate($object);

        if (empty($expectedErrors)) {
            $this->assertCount(0, $errors, 'Aucune erreur attendue');
            return;
        }

        $errorSearch = $errors->findByCodes($expectedErrors['code']);
        
        $this->assertNotEmpty($errorSearch, 'Erreur de validation non trouvée');
        $this->assertEquals(
            $expectedErrors['property'], 
            $errorSearch[0]->getPropertyPath(),
            'Le champ en erreur ne correspond pas'
        );
    }

    /**
     * Helper pour tester plusieurs erreurs
     */
    protected function assertHasValidationError(
        ConstraintViolationListInterface $errors,
        string $property,
        string $message = null
    ): void {
        $found = false;
        
        foreach ($errors as $error) {
            if ($error->getPropertyPath() === $property) {
                $found = true;
                if ($message !== null) {
                    $this->assertStringContainsString(
                        $message,
                        $error->getMessage()
                    );
                }
                break;
            }
        }
        
        $this->assertTrue(
            $found,
            sprintf('Aucune erreur trouvée pour la propriété "%s"', $property)
        );
    }
}
```

---

## 4. Fixtures et Données de Test

### 🎲 Configuration Alice Fixtures

```yaml
# tests/Unit/Fixtures/UserFixtures.yaml
App\Entity\User:
  user_admin:
    username: 'admin'
    password: 'admin123'
    roles: ['ROLE_ADMIN']
  
  user_{1..10}:
    username: '<username()>'
    password: 'password123'
    email: '<email()>'
```

```yaml
# tests/Unit/Fixtures/ArticleFixtures.yaml
include:
  - UserFixtures.yaml

App\Entity\Article:
  article_published_{1..5}:
    title: '<sentence(5)>'
    content: '<text(500)>'
    shortContent: '<text(100)>'
    slug: '<slug()>'
    enabled: true
    user: '@user_<numberBetween(1, 10)>'
    createdAt: '<dateTimeBetween("-1 year", "now")>'
  
  article_draft_{1..3}:
    title: 'Brouillon - <sentence(3)>'
    content: '<text(300)>'
    shortContent: '<text(50)>'
    enabled: false
    user: '@user_admin'
```

### 🔧 Utilisation avec LiipTestFixturesBundle

```php
<?php

namespace App\Tests\Unit\Entity;

use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ArticleEntityTest extends KernelTestCase
{
    private AbstractDatabaseTool $databaseTool;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->databaseTool = self::getContainer()
            ->get(DatabaseToolCollection::class)
            ->get();
        
        // Charge les fixtures par défaut
        $this->databaseTool->loadFixtures();
    }

    public function testWithFixtures(): void
    {
        // Charge des fixtures spécifiques
        $this->databaseTool->loadAliceFixture([
            __DIR__ . '/../Fixtures/ArticleFixtures.yaml'
        ]);

        $em = self::getContainer()->get('doctrine')->getManager();
        $articles = $em->getRepository(Article::class)->findAll();
        
        $this->assertCount(8, $articles); // 5 publiés + 3 brouillons
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        // Nettoie automatiquement la base de données
    }
}
```

### 📊 Fixtures Doctrine Classiques

```php
<?php

namespace App\DataFixtures;

use App\Entity\Article;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');

        // Créer des utilisateurs
        $users = [];
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setUsername($faker->userName)
                 ->setEmail($faker->email)
                 ->setPassword('password123');
            
            $manager->persist($user);
            $users[] = $user;
        }

        // Créer des articles
        foreach ($users as $user) {
            for ($j = 0; $j < rand(1, 5); $j++) {
                $article = new Article();
                $article->setTitle($faker->sentence(6))
                        ->setContent($faker->paragraphs(3, true))
                        ->setShortContent($faker->paragraph())
                        ->setEnabled($faker->boolean(70))
                        ->setUser($user)
                        ->setCreatedAt($faker->dateTimeBetween('-1 year'));
                
                $manager->persist($article);
            }
        }

        $manager->flush();
    }
}
```

---

## 5. Tests d'Intégration

### 🔗 Test Repository avec Base de Données

```php
<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Article;
use App\Repository\ArticleRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ArticleRepositoryTest extends KernelTestCase
{
    private ArticleRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->repository = self::getContainer()
            ->get('doctrine')
            ->getRepository(Article::class);
    }

    public function testFindPaginate(): void
    {
        // Arrange
        $filter = new ArticleFilterDto();
        $filter->setPage(1);
        $filter->setLimit(10);
        $filter->setEnabled(true);

        // Act
        $result = $this->repository->findPaginate($filter);

        // Assert
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('page', $result);
        $this->assertLessThanOrEqual(10, count($result['data']));
    }

    public function testCustomQuery(): void
    {
        // Test une méthode personnalisée du repository
        $articles = $this->repository->findPublishedBetweenDates(
            new \DateTime('-1 month'),
            new \DateTime('now')
        );

        $this->assertIsArray($articles);
        foreach ($articles as $article) {
            $this->assertTrue($article->isEnabled());
            $this->assertGreaterThan(
                new \DateTime('-1 month'),
                $article->getCreatedAt()
            );
        }
    }
}
```

### 🎭 Test Service/Mapper

```php
<?php

namespace App\Tests\Integration\Mapper;

use App\Dto\Article\CreateArticleDto;
use App\Entity\Article;
use App\Entity\User;
use App\Mapper\ArticleMapper;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ArticleMapperTest extends KernelTestCase
{
    private ArticleMapper $mapper;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = self::getContainer();
        
        $this->mapper = $container->get(ArticleMapper::class);
        $this->userRepository = $container->get(UserRepository::class);
    }

    public function testDtoToEntity(): void
    {
        // Arrange
        $user = $this->userRepository->find(1);
        $dto = new CreateArticleDto(
            title: 'Test Article',
            content: 'Contenu de test',
            shortContent: 'Court',
            enabled: true,
            user: $user->getId()
        );

        // Act
        $entity = $this->mapper->toEntity($dto);

        // Assert
        $this->assertInstanceOf(Article::class, $entity);
        $this->assertEquals('Test Article', $entity->getTitle());
        $this->assertEquals('test-article', $entity->getSlug());
        $this->assertEquals($user, $entity->getUser());
    }

    public function testEntityToDto(): void
    {
        // Arrange
        $user = new User();
        $user->setUsername('testuser');
        
        $article = new Article();
        $article->setTitle('Mon Article')
                ->setContent('Contenu')
                ->setShortContent('Court')
                ->setEnabled(true)
                ->setUser($user);

        // Act
        $dto = $this->mapper->toDto($article);

        // Assert
        $this->assertEquals('Mon Article', $dto->getTitle());
        $this->assertTrue($dto->isEnabled());
    }
}
```

---

## 6. Tests Fonctionnels

### 🌐 Test Controller/API

```php
<?php

namespace App\Tests\Functional\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ArticleControllerTest extends WebTestCase
{
    private $client;
    private $token;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->authenticateUser();
    }

    private function authenticateUser(): void
    {
        // Login pour obtenir un token JWT
        $this->client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'username' => 'admin',
            'password' => 'admin123'
        ]));

        $response = json_decode(
            $this->client->getResponse()->getContent(), 
            true
        );
        $this->token = $response['token'];
    }

    public function testListArticles(): void
    {
        // Requête avec authentification JWT
        $this->client->request('GET', '/api/admin/articles', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $data = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertIsArray($data['data']);
    }

    public function testCreateArticle(): void
    {
        $articleData = [
            'title' => 'Nouvel Article Test',
            'content' => 'Contenu de test complet',
            'shortContent' => 'Résumé court',
            'enabled' => true,
            'user' => 1
        ];

        $this->client->request('POST', '/api/admin/articles', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($articleData));

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        
        $response = json_decode(
            $this->client->getResponse()->getContent(), 
            true
        );
        $this->assertEquals('Nouvel Article Test', $response['title']);
        $this->assertArrayHasKey('id', $response);
    }

    public function testValidationErrors(): void
    {
        $invalidData = [
            'title' => '', // Titre vide
            'content' => 'abc', // Trop court
            'shortContent' => '',
            'user' => 999 // User inexistant
        ];

        $this->client->request('POST', '/api/admin/articles', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $this->token,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($invalidData));

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        
        $errors = json_decode(
            $this->client->getResponse()->getContent(), 
            true
        );
        $this->assertArrayHasKey('errors', $errors);
    }

    public function testUnauthorizedAccess(): void
    {
        // Sans token
        $this->client->request('GET', '/api/admin/articles');
        
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }
}
```

### 🔒 Test de Sécurité

```php
<?php

namespace App\Tests\Functional\Security;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class SecurityTest extends WebTestCase
{
    public function testLoginSuccess(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'username' => 'admin',
            'password' => 'admin123'
        ]));

        $this->assertResponseIsSuccessful();
        
        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);
    }

    public function testLoginFailure(): void
    {
        $client = static::createClient();
        
        $client->request('POST', '/api/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'username' => 'admin',
            'password' => 'wrongpassword'
        ]));

        $this->assertResponseStatusCodeSame(401);
    }

    public function testProtectedRoute(): void
    {
        $client = static::createClient();
        
        // Sans authentification
        $client->request('GET', '/api/admin/users');
        $this->assertResponseStatusCodeSame(401);
        
        // Avec token invalide
        $client->request('GET', '/api/admin/users', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer invalid_token',
        ]);
        $this->assertResponseStatusCodeSame(401);
    }
}
```

---

## 7. Couverture de Code

### 📈 Génération du Rapport de Couverture

```bash
# Installation de Xdebug (nécessaire pour la couverture)
pecl install xdebug

# Configuration dans php.ini
xdebug.mode=coverage

# Générer le rapport HTML
vendor/bin/phpunit --coverage-html coverage/

# Générer le rapport XML (pour CI/CD)
vendor/bin/phpunit --coverage-clover coverage.xml

# Afficher la couverture en console
vendor/bin/phpunit --coverage-text
```

### 🎯 Annotations de Couverture

```php
<?php

namespace App\Service;

class ArticleService
{
    /**
     * @codeCoverageIgnore
     */
    public function debugMethod(): void
    {
        // Cette méthode ne sera pas comptée dans la couverture
    }

    public function importantMethod(): void
    {
        // Cette méthode doit être testée
    }
}

// Dans le test
class ArticleServiceTest extends TestCase
{
    /**
     * @covers \App\Service\ArticleService::importantMethod
     */
    public function testImportantMethod(): void
    {
        // Test spécifiquement cette méthode
    }

    /**
     * @coversNothing
     */
    public function testIntegration(): void
    {
        // Ce test n'affecte pas la couverture
    }
}
```

---

## 8. Bonnes Pratiques

### ✅ Principes FIRST

- **F**ast : Les tests doivent être rapides
- **I**ndependent : Pas de dépendance entre tests
- **R**epeatable : Même résultat à chaque exécution
- **S**elf-Validating : Pass ou Fail, pas d'interprétation
- **T**imely : Écrits au bon moment (TDD idéalement)

### 🎯 Pattern AAA (Arrange-Act-Assert)

```php
public function testExample(): void
{
    // Arrange : Préparer les données
    $article = new Article();
    $article->setTitle('Test');

    // Act : Exécuter l'action
    $result = $article->getSlug();

    // Assert : Vérifier le résultat
    $this->assertEquals('test', $result);
}
```

### 📝 Naming Convention

```php
// ✅ BON : Descriptif et clair
public function testArticleSlugIsGeneratedFromTitle(): void
public function testUserCannotAccessAdminWithoutAuthentication(): void

// ❌ MAUVAIS : Pas assez descriptif
public function testSlug(): void
public function test1(): void
```

### 🔍 Assertions Utiles

```php
// Assertions de base
$this->assertTrue($condition);
$this->assertFalse($condition);
$this->assertEquals($expected, $actual);
$this->assertSame($expected, $actual); // Comparaison stricte
$this->assertNull($value);
$this->assertNotNull($value);

// Arrays
$this->assertCount(3, $array);
$this->assertContains('value', $array);
$this->assertArrayHasKey('key', $array);
$this->assertEmpty($array);

// Strings
$this->assertStringContainsString('substring', $string);
$this->assertStringStartsWith('prefix', $string);
$this->assertMatchesRegularExpression('/pattern/', $string);

// Objects
$this->assertInstanceOf(Article::class, $object);
$this->assertObjectHasProperty('title', $article);

// Exceptions
$this->expectException(\InvalidArgumentException::class);
$this->expectExceptionMessage('Message attendu');
$this->expectExceptionCode(404);

// Response (Symfony)
$this->assertResponseIsSuccessful();
$this->assertResponseStatusCodeSame(200);
$this->assertResponseRedirects('/login');
$this->assertSelectorTextContains('h1', 'Titre');
```

### 🚫 Anti-patterns à Éviter

```php
// ❌ MAUVAIS : Test qui teste plusieurs choses
public function testArticle(): void
{
    $article = new Article();
    $this->assertNotNull($article);
    
    $article->setTitle('Test');
    $this->assertEquals('Test', $article->getTitle());
    
    $article->setContent('Content');
    $this->assertEquals('Content', $article->getContent());
    
    // Trop de choses dans un seul test
}

// ✅ BON : Un test par comportement
public function testArticleCanBeCreated(): void
{
    $article = new Article();
    $this->assertNotNull($article);
}

public function testArticleStoresTitle(): void
{
    $article = new Article();
    $article->setTitle('Test');
    $this->assertEquals('Test', $article->getTitle());
}
```

---

## 9. Exercices Pratiques

### 📚 Exercice 1 : Test d'Entity

**Objectif** : Créer des tests pour l'entité User

```php
// À implémenter : tests/Unit/Entity/UserTest.php
class UserTest extends TestCase
{
    // TODO: Tester que le username est requis
    // TODO: Tester la validation de l'email
    // TODO: Tester le hashage du mot de passe
    // TODO: Tester l'ajout/suppression de rôles
}
```

### 📚 Exercice 2 : Test de Repository

**Objectif** : Tester une méthode de recherche personnalisée

```php
// À implémenter : tests/Integration/Repository/ArticleRepositoryTest.php
class ArticleRepositoryTest extends KernelTestCase
{
    // TODO: Tester findByAuthor()
    // TODO: Tester findPublishedLastWeek()
    // TODO: Tester la pagination
    // TODO: Tester les filtres de recherche
}
```

### 📚 Exercice 3 : Test API Complet

**Objectif** : Tester le CRUD complet d'une ressource

```php
// À implémenter : tests/Functional/Api/ArticleApiTest.php
class ArticleApiTest extends WebTestCase
{
    // TODO: Test GET /api/articles (liste)
    // TODO: Test GET /api/articles/{id} (détail)
    // TODO: Test POST /api/articles (création)
    // TODO: Test PUT /api/articles/{id} (modification)
    // TODO: Test DELETE /api/articles/{id} (suppression)
    // TODO: Test des erreurs de validation
    // TODO: Test des permissions (admin only)
}
```

### 📚 Exercice 4 : Création d'un Mock

**Objectif** : Tester un service avec dépendances

```php
// Service à tester
class NotificationService
{
    public function __construct(
        private MailerInterface $mailer,
        private UserRepository $userRepository
    ) {}

    public function notifyNewArticle(Article $article): void
    {
        $users = $this->userRepository->findSubscribed();
        foreach ($users as $user) {
            $this->mailer->send(
                $user->getEmail(),
                'Nouvel article: ' . $article->getTitle()
            );
        }
    }
}

// Test avec mocks
class NotificationServiceTest extends TestCase
{
    public function testNotifyNewArticle(): void
    {
        // TODO: Créer un mock du MailerInterface
        // TODO: Créer un mock du UserRepository
        // TODO: Vérifier que send() est appelé pour chaque user
        // TODO: Vérifier les paramètres passés à send()
    }
}
```

---

## 🎓 Commandes Utiles Récapitulatives

```bash
# Lancer tous les tests
vendor/bin/phpunit

# Lancer une suite spécifique
vendor/bin/phpunit --testsuite=unit

# Lancer un fichier de test
vendor/bin/phpunit tests/Unit/Entity/ArticleTest.php

# Lancer une méthode spécifique
vendor/bin/phpunit --filter testSlugGeneration

# Mode verbose avec détails
vendor/bin/phpunit --testdox

# Avec couverture de code
vendor/bin/phpunit --coverage-html coverage/
vendor/bin/phpunit --coverage-text

# Arrêter au premier échec
vendor/bin/phpunit --stop-on-failure

# Relancer seulement les tests échoués
vendor/bin/phpunit --order-by=defects --stop-on-defect

# Parallélisation des tests
vendor/bin/phpunit --process-isolation

# Debug d'un test
vendor/bin/phpunit --debug

# Générer un rapport JUnit (pour CI/CD)
vendor/bin/phpunit --log-junit report.xml
```

---

## 📖 Ressources Supplémentaires

- [Documentation PHPUnit](https://phpunit.de/documentation.html)
- [Symfony Testing Documentation](https://symfony.com/doc/current/testing.html)
- [Test Driven Development (TDD)](https://www.php.net/manual/fr/book.phpunit.php)
- [Fixtures Bundle Documentation](https://github.com/liip/LiipTestFixturesBundle)
- [Alice Documentation](https://github.com/nelmio/alice)

---

## 🏆 Checklist Projet

- [ ] Configuration PHPUnit fonctionnelle
- [ ] Structure de dossiers tests organisée
- [ ] Tests unitaires pour toutes les entités
- [ ] Tests unitaires pour tous les DTOs
- [ ] Tests d'intégration pour les repositories
- [ ] Tests d'intégration pour les services/mappers
- [ ] Tests fonctionnels pour tous les endpoints API
- [ ] Tests de sécurité (authentification/autorisation)
- [ ] Fixtures de test complètes
- [ ] Couverture de code > 80%
- [ ] Tests automatisés dans CI/CD
- [ ] Documentation des tests à jour

Ce cours vous donne toutes les bases pour implémenter une stratégie de tests complète dans votre application Symfony !