<?php

declare(strict_types=1);

namespace App\Handler;

interface DeleteUserHandlerInterface
{
    public function handle(int $id): void;
}
