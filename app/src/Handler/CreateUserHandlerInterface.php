<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\User;

interface CreateUserHandlerInterface
{
    public function handle(User $input): User;
}
