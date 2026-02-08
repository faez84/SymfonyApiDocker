<?php

namespace App\Tests\Handler;

use App\Entity\User;
use App\Handler\DeleteUserHandler;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DeleteUserHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $userId = 1;
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

        $entityManager->expects(self::once())
        ->method('remove')
        ->with($user);

        $entityManager->expects(self::once())
        ->method('flush');

        $handler = new DeleteUserHandler($entityManager);
        $handler->handle($userId);
    }
    public function testHandleWithNonExistentUser(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('User not found');
        $userId = 1;
        $user = $this->createMock(User::class);
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

        $entityManager->expects(self::never())
        ->method('remove')
        ->with($user);

        $entityManager->expects(self::never())
        ->method('flush');

        $handler = new DeleteUserHandler($entityManager);
        $handler->handle($userId);
    }
}