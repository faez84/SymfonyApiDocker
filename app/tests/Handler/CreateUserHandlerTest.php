<?php

namespace App\Tests\Handler;

use App\Entity\User;
use App\Exception\EmailAlreadyExists;
use App\Handler\CreateUserHandler;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateUserHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $email = 'test@test.com';
        $password = '111' ;
        $hashedPassword = '222';
        $persistedUser = null;
        $entityManager = $this->createMock(EntityManagerInterface::class);
        
        $repo = $this->createMock(UserRepository::class);
        $hasher = $this->createMock(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail($email);
        $user->setPassword($password);

        $repo->expects(self::once())
        ->method('findBy')
        ->with(['email' => $email])
        ->willReturn([]);

 
        $entityManager->expects(self::once())
            ->method('persist')
            ->with(self::callback(function (User $user) use (&$persistedUser, $email): bool {
                $persistedUser = $user;
                return $user->getEmail() === $email;
            }));
        $entityManager->expects(self::once())
        ->method('flush');

        $hasher->expects(self::once())
        ->method('hashPassword')
        ->with(self::isInstanceOf(User::class), $password)
        ->willreturn($hashedPassword);

        $handler = new CreateUserHandler($repo, $entityManager, $hasher);
        $handler->handle($user);

    }
    public function testHandleWithEmailAlreadyExists(): void
    {
        $email = 'test@test.com';
        $password = '111' ;
        $this->expectException(EmailAlreadyExists::class);
        $this->expectExceptionMessage($email);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        
        $repo = $this->createMock(UserRepository::class);
        $hasher = $this->createMock(UserPasswordHasherInterface::class);

        $user = new User();
        $user->setEmail($email);
        $user->setPassword($password);

        $repo->expects(self::once())
            ->method('findBy')
            ->with(['email' => $email])
            ->willReturn([$user]);
        
        $entityManager->expects(self::never())
            ->method('persist');
        $entityManager->expects(self::never())
            ->method('flush');
        $hasher->expects(self::never())
            ->method('hashPassword')
            ->with(self::isInstanceOf(User::class), $password);

        $handler = new CreateUserHandler($repo, $entityManager, $hasher);
        $handler->handle($user);
    }
}