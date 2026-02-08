<?php

declare(strict_types=1);

namespace App\Handler;

interface UploadUserPhotoHanderInterface
{
    public function handle(int $userId, string $filename): void;
}
