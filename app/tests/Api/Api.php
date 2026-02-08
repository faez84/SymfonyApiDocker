<?php
namespace App\Tests\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class Api extends WebTestCase
{
    protected ?KernelBrowser $client = null;
    protected EntityManagerInterface $em;

    private const TEST_USER_EMAIL = 'testuser@example.com';
    private const TEST_USER_PASSWORD = 'testpassword';
    private const TEST_USER_ROLE = 'ROLE_USER';
    private const TEST_USER_ADMIN_EMAIL = 'admin@example.com';
    private const TEST_USER_ADMIN_PASSWORD = 'adminpassword';
    private const TEST_ADMIN_ROLE = 'ROLE_ADMIN';
    private const JSON_CONTENT_TYPES = [
        'application/json',
        'application/problem+json',
    ];

    public function setUp(): void
    {
        parent::setUp();
        if (!$this->client) {
            $this->client = static::createClient();
        }
        $this->em = static::getContainer()->get('doctrine.orm.entity_manager');
        $this->createUser();
        $this->createAdminUser();
    }
    protected function tearDown(): void
    {
        $this->deleteAdminUser();
        $this->deleteUser();
        parent::tearDown();
    }
    
    public function getToken(): string
    {
        $this->client->request('POST', '/api/login_check', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ], json_encode([
            'username' => self::TEST_USER_ADMIN_EMAIL,
            'password' => self::TEST_USER_ADMIN_PASSWORD,
        ]));

        $this->assertResponseIsSuccessful();
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);

        return $data['token'];
    }

    protected function assertJsonLikeResponse(): void
    {
        $contentType = (string) $this->client->getResponse()->headers->get('Content-Type', '');
        $matches = false;
        foreach (self::JSON_CONTENT_TYPES as $type) {
            if (stripos($contentType, $type) !== false) {
                $matches = true;
                break;
            }
        }

        $this->assertTrue(
            $matches,
            sprintf('Expected JSON response Content-Type, got "%s".', $contentType)
        );
    }

    protected function assertProblemDetailsResponse(int $expectedStatus): void
    {
        $this->assertJsonLikeResponse();
        $this->assertResponseStatusCodeSame($expectedStatus);

        $data = json_decode((string) $this->client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }

    public function createUser(): void
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => self::TEST_USER_EMAIL]);
        if (!$user) {
            $user = new User();
            $user->setEmail(self::TEST_USER_EMAIL);
            $user->setPassword($this->hashPassword($user, self::TEST_USER_PASSWORD));
            $user->setRoles([self::TEST_USER_ROLE]);
            $this->em->persist($user);
            $this->em->flush();
        }   
    }

    public function deleteUser(): void
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => self::TEST_USER_EMAIL]);
        if ($user) {
            $this->em->remove($user);
            $this->em->flush();
        }   
    }

    public function createAdminUser(): void
    {
        $admin = $this->em->getRepository(User::class)->findOneBy(['email' => self::TEST_USER_ADMIN_EMAIL]);
        if (!$admin) {
            $admin = new User();
            $admin->setEmail(self::TEST_USER_ADMIN_EMAIL);
            $admin->setPassword($this->hashPassword($admin, self::TEST_USER_ADMIN_PASSWORD));
            $admin->setRoles([self::TEST_ADMIN_ROLE]);
            $this->em->persist($admin);
            $this->em->flush();
        }   
    }

    public function deleteAdminUser(): void
    {
        $admin = $this->em->getRepository(User::class)->findOneBy(['email' => self::TEST_USER_ADMIN_EMAIL]);
        if ($admin) {
            $this->em->remove($admin);
            $this->em->flush();
        }   
    }

        public function deleteUserWithEmail(string $email): void
    {
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($user) {
            $this->em->remove($user);
            $this->em->flush();
        }   
    }

    private function hashPassword(User $user, string $plainPassword): string
    {
        /** @var UserPasswordHasherInterface $hasher */
        $hasher = static::getContainer()->get('security.user_password_hasher');
        return $hasher->hashPassword($user, $plainPassword);
    }
}
