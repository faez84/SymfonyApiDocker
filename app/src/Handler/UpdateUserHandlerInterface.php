<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\User;

interface UpdateUserHandlerInterface
{
    public function handle(int $id, User $input): User;
}
