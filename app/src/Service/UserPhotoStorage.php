<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class UserPhotoStorage
{
    public function __construct(
        private string $userPhotosDir,
        private SluggerInterface $slugger
    ) {
    }
    public function store(UploadedFile $file, int $userId): string
    {

        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $slugerNamer = strtolower((string) $this->slugger->slug($originalFilename));

        $xtension = strtolower((string) $file->guessExtension());
        if (!$xtension) {
            $xtension = 'bin';
        }

        $filename = sprintf('user_%d_%s.%s', $userId, $slugerNamer, $xtension);
        $file->move($this->userPhotosDir, $filename);

        return $filename;
    }
}
