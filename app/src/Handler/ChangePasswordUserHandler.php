<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ChangePasswordUserHandler implements ChangePasswordUserHandlerInterface
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function handle(int $id, User $input): User
    {
        $user = $this->em->getRepository(User::class)->find($id);
        if (!$user) {
            throw new NotFoundHttpException('User not found');
        }

        if (!$this->passwordHasher->isPasswordValid($user, $input->getPassword())) {
            throw new UnprocessableEntityHttpException('Password is not correct');
        }

        $hashedPass = $this->passwordHasher->hashPassword($user, $input->getNewPassword());
        $user->setPassword($hashedPass);
        $this->em->flush();

        return $user;
    }
}
