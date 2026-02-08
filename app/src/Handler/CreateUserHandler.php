<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\User;
use App\Exception\EmailAlreadyExists;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class CreateUserHandler implements CreateUserHandlerInterface
{
    public function __construct(
        private UserRepository $users,
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $hasher
    ) {
    }

    public function handle(User $input): User
    {
        if ($this->users->findBy(['email' => $input->getEmail()])) {
            throw new EmailAlreadyExists($input->getEmail());
        }

        $user = new User();
        $user->setEmail($input->getEmail());
        $user->setPassword($this->hasher->hashPassword($user, $input->getPassword()));
        $user->setRoles(['ROLE_USER']);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
