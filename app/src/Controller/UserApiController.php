<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Handler\ChangePasswordUserHandler;
use App\Handler\ChangePasswordUserHandlerInterface;
use App\Handler\CreateUserHandler;
use App\Handler\CreateUserHandlerInterface;
use App\Handler\DeleteUserHandler;
use App\Handler\DeleteUserHandlerInterface;
use App\Handler\UpdateUserHandler;
use App\Handler\UpdateUserHandlerInterface;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserApiController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
        #[Autowire(service: CreateUserHandler::class)]
        private CreateUserHandlerInterface $createUserHandler,
        #[Autowire(service: UpdateUserHandler::class)]
        private UpdateUserHandlerInterface $updateUserHandler,
        #[Autowire(service: ChangePasswordUserHandler::class)]
        private ChangePasswordUserHandlerInterface $changePasswordUserHandler,
        #[Autowire(service: DeleteUserHandler::class)]
        private DeleteUserHandlerInterface $deleteUserHandler
    ) {
    }

    #[Route('/api/users', name: 'api_add_user', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN')]
    public function adduser(Request $request): JsonResponse
    {
        $serializedUser = $this->deserializeOr400($request->getContent(), User::class, ['groups' => ['user:create']]);

        $errors = $this->validateOr422($serializedUser, ['user:create']);
        if ($errors) {
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $this->createUserHandler->handle($serializedUser);

        return $this->json(
            [
                'status' => 'User created',
                'id' => $user->getId()],
            Response::HTTP_CREATED
        );
    }

    #[Route('/api/users/{id}', name: 'api_update_user', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function updateUser(Request $request, int $id): JsonResponse
    {
        $serializedUser = $this->deserializeOr400($request->getContent(), User::class, ['groups' => ['user:update']]);

        $errors = $this->validateOr422($serializedUser, ['user:update']);
        if ($errors) {
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->updateUserHandler->handle($id, $serializedUser);

        return $this->json(['status' => 'User updated', 'id' => $id], Response::HTTP_OK);
    }

    #[Route('/api/users/{id}/password', name: 'api_change_password', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN')]
    public function changePassword(Request $request, int $id): JsonResponse
    {
        $serializedUser = $this->deserializeOr400(
            $request->getContent(),
            User::class,
            ['groups' => ['user:password_update']]
        );

        $errors = $this->validateOr422($serializedUser, ['user:password_update']);
        if ($errors) {
            return $this->json(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $this->changePasswordUserHandler->handle($id, $serializedUser);

        return $this->json(['status' => 'Password changed'], Response::HTTP_OK);
    }

    #[Route('/api/users/{id}', name: 'api_delete_user', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteUser(int $id): JsonResponse
    {

        $this->deleteUserHandler->handle($id);

        return $this->json(['status' => 'User deleted'], Response::HTTP_OK);
    }

    #[Route('/api/users', name: 'api_get_all_users', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getAllUsers(Request $request, UserRepository $repo): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(100, max(1, (int) $request->query->get('limit', 20)));
        $offset = ($page - 1) * $limit;

        $users = $repo->findBy([], ['id' => 'DESC'], $limit, $offset);

        return $this->json($users, Response::HTTP_OK, [], ['groups' => ['user:list']]);
    }

    #[Route('/api/users/{id}', name: 'api_user_details', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]

    public function getUserDetails(User $user): JsonResponse
    {
        return $this->json($user, Response::HTTP_OK, [], ['groups' => ['user:list']]);
    }
    /**
     * @param array<string, mixed> $context
     */
    private function deserializeOr400(string $json, string $class, array $context = []): object
    {
        try {
            $data = $this->serializer->deserialize($json, $class, 'json', $context);
        } catch (NotEncodableValueException $e) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException(
                'Invalid JSON payload.',
                $e
            );
        }

        if (!\is_object($data)) {
            throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Invalid request body.');
        }

        return $data;
    }

    /**
     * @param array<int, string> $groups
     * @return array<string, array<int, string>>
     */
    private function validateOr422(object $data, array $groups = []): array
    {
        /** @var ConstraintViolationListInterface $violations */
        $violations = $this->validator->validate(
            $data,
            null,
            $groups !== [] ? $groups : null
        );

        if (\count($violations) === 0) {
            return [];
        }

        $errors = [];

        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation) {
            $path = (string) $violation->getPropertyPath();
            $path = $path !== '' ? $path : '_global';
            $errors[$path][] = $violation->getMessage();
        }

        return $errors;
    }
}
