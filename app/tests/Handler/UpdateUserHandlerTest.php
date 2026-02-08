<?php

namespace App\Tests\Handler;

use App\Entity\User;
use App\Handler\UpdateUserHandler;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
 
class UpdateUserHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $userId = 1;
        $entityManager = $this->createMock(EntityManagerInterface::class);
        
        $repo = $this->createMock(EntityRepository::class);
        $entityManager->expects(self::once())
        ->method('getRepository')
        ->with(User::class)
        ->willReturn($repo);

        $user = new User();
        $user->setEmail('oldEmail@test.com');
 
        $repo->expects(self::once())
        ->method('find')
        ->with($userId)
        ->willReturn($user);

        $entityManager->expects(self::once())
        ->method('flush');

        $handler = new UpdateUserHandler($entityManager);
        $handler->handle($userId, $user);
    }

    public function testHandleWithNotFoundHttpException(): void
    {
        $userId = 1;
        $repo = $this->createStub(EntityRepository::class);

        $repo->method('find')->willReturn(null);

        $em = $this->createMock(EntityManagerInterface::class);
        $em->method('getRepository')->with(User::class)->willReturn($repo);
        $em->expects(self::never())->method('flush');
        $handler = new UpdateUserHandler($em);

        $this->expectException(NotFoundHttpException::class);
        $handler->handle($userId, new User());
    }
}