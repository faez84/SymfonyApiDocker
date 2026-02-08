<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Handler\UploadUserPhotoHanderInterface;
use App\Handler\UploadUserPhotoHandler;
use App\Service\UserPhotoStorage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProfileController extends AbstractController
{
    public function __construct(
        private UserPhotoStorage $photoStorage,
        private ValidatorInterface $validationService,
        #[Autowire(service: UploadUserPhotoHandler::class)]
        private UploadUserPhotoHanderInterface $uploadUserPhotoHandler
    ) {
    }
    #[Route('/api/profile', name: 'api_user_profile', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function getUserProfile(#[CurrentUser()] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json($user, Response::HTTP_OK, [], ['groups' => ['user:profile']]);
    }

    #[Route('/api/profile/photo', name: 'api_update_user_profile', methods: ['POST'])]
    public function updateUserProfile(Request $request, #[CurrentUser()] ?User $user): JsonResponse
    {
        $file = $request->files->get('photo');

        if (!$file instanceof UploadedFile) {
            return $this->json(
                ['error' => 'Missing file field "photo" (multipart/form-data).'],
                Response::HTTP_BAD_REQUEST
            );
        }
        $erros = $this->validationService->validate($file);
        if (count($erros) > 0) {
            $errorMessages = [];

            foreach ($erros as $error) {
                $errorMessages[] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $filename = $this->photoStorage->store($file, $user->getId());
        $this->uploadUserPhotoHandler->handle($user->getId(), $filename);

        return $this->json(['message' => 'Photo uploaded successfully'], Response::HTTP_OK);
    }
}
