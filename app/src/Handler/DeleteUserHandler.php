<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DeleteUserHandler implements DeleteUserHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    public function handle(int $id): void
    {
        $user = $this->em->getRepository(User::class)->find($id);
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }
        $this->em->remove($user);
        $this->em->flush();
    }
}
