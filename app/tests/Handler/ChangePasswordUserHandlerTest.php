<?php

namespace App\Test\Handler;

use App\Entity\User;
use App\Handler\ChangePasswordUserHandler;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ChangePasswordUserHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $userId = 1;
        $password = '111' ;
        $newPassword = '222';
        $hashedPassword = '333';
        $user = $this->createMock(User::class);
        $repo = $this->createMock(EntityRepository::class);
        $hasher = $this->createMock(UserPasswordHasherInterface::class);

        $repo->expects(self::once())
        ->method('find')
        ->with($userId)
        ->willReturn($user);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())
        ->method('getRepository')
        ->with(User::class)
        ->willReturn($repo);

        $hasher->expects(self::once())
        ->method('isPasswordValid')
        ->with(self::isInstanceOf(User::class), $password)
        ->willreturn(true);

        $user = new User();
        $user->setPassword($password);
        $user->setNewPassword($newPassword);

        $hasher->expects(self::once())
        ->method('hashPassword')
        ->with(self::isInstanceOf(User::class), $newPassword)
        ->willreturn($hashedPassword);

        
        $handler = new ChangePasswordUserHandler($entityManager, $hasher);
        $handler->handle($userId, $user);
    }

    public function testHandleWithNotFoundHttpException(): void
    {
        $userId = 1;

        $repo = $this->createMock(EntityRepository::class);
        $repo->expects(self::once())
            ->method('find')
            ->with($userId)
            ->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($repo);

        $hasher = $this->createMock(UserPasswordHasherInterface::class);
        $hasher->expects(self::never())->method('isPasswordValid');
        $hasher->expects(self::never())->method('hashPassword');

        $handler = new ChangePasswordUserHandler($entityManager, $hasher);
        $this->expectException(NotFoundHttpException::class);
        $handler->handle($userId, new User());
    }

    public function testHandleWithUnprocessableEntityHttpException(): void
    {
        $userId = 1;
        $password = '111' ;
        $newPassword = '222';
        $user = $this->createMock(User::class);
        $repo = $this->createMock(EntityRepository::class);
        $repo->expects(self::once())
            ->method('find')
            ->with($userId)
            ->willReturn($user);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($repo);

        $hasher = $this->createMock(UserPasswordHasherInterface::class);

        $hasher->expects(self::once())
        ->method('isPasswordValid')
        ->with(self::isInstanceOf(User::class), $password)
        ->willreturn(false);

        $hasher->expects(self::never())->method('hashPassword');
        $user = new User();
        $user->setPassword($password);
        $user->setNewPassword($newPassword);

        $handler = new ChangePasswordUserHandler($entityManager, $hasher);
        $this->expectException(UnprocessableEntityHttpException::class);
        $handler->handle($userId, $user);
    }
}