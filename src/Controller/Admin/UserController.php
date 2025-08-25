<?php

namespace App\Controller\Admin;

use App\Dto\User\UpdateUserAdminDto;
use App\Entity\User;
use App\Mapper\UserMapper;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/admin/users', name: 'api_admin_users')]
class UserController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private readonly UserRepository $userRepository,
        private readonly UserMapper $userMapper,
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json(
            $this->userRepository->findAll(),
            Response::HTTP_OK,
            context: [
                'groups' => ['common:index', 'users:admin:read'],
            ]
        );
    }

    #[Route('/{id}', name: 'update', methods: ['PATCH'])]
    public function update(
        User $user,
        #[MapRequestPayload]
        UpdateUserAdminDto $dto
    ): JsonResponse {
        $user = $this->userMapper->map($dto, $user);
        $this->em->flush();
        return $this->json(
            $user,
            Response::HTTP_OK,
            context: [
                'groups' => ['common:index', 'users:index', 'users:show'],
            ]
        );
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(
        User $user,
    ): JsonResponse {
        $this->em->remove($user);
        $this->em->flush();
        return $this->json(
            null,
            Response::HTTP_NO_CONTENT,
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(User $user): JsonResponse
    {
        return $this->json(
            $user,
            Response::HTTP_OK,
            context: [
                'groups' => ['common:index', 'users:index', 'users:show'],
            ]
        );

    }
}
