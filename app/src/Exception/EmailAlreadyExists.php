<?php

declare(strict_types=1);

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class EmailAlreadyExists extends \DomainException implements HttpExceptionInterface
{
    public function __construct(string $email)
    {
        parent::__construct(sprintf('Email "%s" already exists.', $email));
    }
    public function getStatusCode(): int
    {
        return 409;
    }

    /**
     * @return array<string>
     */
    public function getHeaders(): array
    {
        return [];
    }
}
