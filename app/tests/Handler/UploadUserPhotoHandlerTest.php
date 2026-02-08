<?php

namespace App\Tests\Handler;

use App\Entity\User;
use App\Handler\UploadUserPhotoHandler;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Monolog\Test\TestCase;

class UploadUserPhotoHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $userId = 1;
        $filename = 'user_10_avatar.webp';
        $entityManger = $this->createMock(EntityManagerInterface::class);
        $objectRepositoyr = $this->createMock(EntityRepository::class);

        $user = $this->createMock(User::class);
        
        $user->expects($this->once())
            ->method('setPhoto')
            ->with($filename);

        $objectRepositoyr->expects(self::once())
            ->method('find')
            ->with($userId)
            ->willReturn($user);

        $entityManger->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($objectRepositoyr);

        $entityManger->expects($this->once())
            ->method('persist');
        $entityManger->expects($this->once())
            ->method('flush');

        $handler = new UploadUserPhotoHandler($entityManger);
        $handler->handle($userId, $filename);
    }

    public function testHandleDoesNothingWhenUserNotFound(): void
    {
        $userId = 1;
        $filename = 'user_10_avatar.webp';
        $entityManger = $this->createMock(EntityManagerInterface::class);
        $objectRepositoyr = $this->createMock(EntityRepository::class);

        $objectRepositoyr->expects(self::once())
            ->method('find')
            ->willReturn(null);
        $entityManger->expects($this->once())
            ->method('getRepository')
            ->with(User::class)
            ->willReturn($objectRepositoyr);

        $entityManger->expects($this->never())
            ->method('persist');
        $entityManger->expects($this->never())
            ->method('flush');
        $handler = new UploadUserPhotoHandler($entityManger);
        $handler->handle($userId, $filename);
    }
}