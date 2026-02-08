<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UploadUserPhotoHandler implements UploadUserPhotoHanderInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }
    public function handle(int $userId, string $filename): void
    {
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        if ($user) {
            $user->setPhoto($filename);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
    }
}
