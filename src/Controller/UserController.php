<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\JsonException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/api/users/{id}', name: 'app_users_index', defaults: ['id' => ''], methods: ['GET'])]
    public function index(string $id, UserRepository $userRepository): JsonResponse
    {
        if ($id === '') {
            $users = $userRepository->findBy([], ['created' => 'ASC']);

            $result = [];
            foreach ($users as $user) {
                $result[] = $user->toArray();
            }

            return new JsonResponse($result);
        }

        if (null === $user = $userRepository->find($id)) {
            return new JsonResponse(['message' => 'No user with this id was found.'], 404);
        }

        return new JsonResponse($user->toArray());
    }

    #[Route('/api/users', name: 'app_users_create', methods: ['POST'])]
    public function create(Request $request, UserRepository $userRepository): JsonResponse
    {
        try {
            $body = $request->toArray();
        } catch (JsonException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 400);
        }

        $email = $body['email'] ?? null;
        $password = $body['password'] ?? null;

        if ($email === null || $password === null) {
            return new JsonResponse(['message' => 'Email and password must both be set.'], 400);
        }

        if (strlen($password) < 10) {
            return new JsonResponse(['message' => 'The password must be at least 10 characters long.'], 400);
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            return new JsonResponse(['message' => 'This is not a valid email.'], 400);
        }

        if (null !== $userRepository->findOneBy(['email' => $email])) {
            return new JsonResponse(['message' => 'Another user with this email already exists.'], 409);
        }

        $user = new User();
        $user->setEmail($email);
        $user->setPassword(password_hash($password, PASSWORD_DEFAULT));

        $userRepository->add($user, true);

        return new JsonResponse($user->toArray(), 200);
    }

    #[Route('/api/users/{id}', name: 'app_users_update', methods: ['PATCH'])]
    public function update(string $id, Request $request, UserRepository $userRepository): JsonResponse
    {
        if (null === $user = $userRepository->find($id)) {
            return new JsonResponse(['message' => 'A user with this id does not exist.'], 404);
        }

        try {
            $body = $request->toArray();
        } catch (JsonException $e) {
            return new JsonResponse(['message' => $e->getMessage()], 400);
        }

        $email = $body['email'] ?? null;
        $password = $body['password'] ?? null;

        if ($email !== null) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                return new JsonResponse(['message' => 'This is not a valid email.'], 400);
            }

            $user->setEmail($email);
        }

        if ($password !== null) {
            if (strlen($password) < 10) {
                return new JsonResponse(['message' => 'The password must be at least 10 characters long.'], 400);
            }

            $user->setPassword(password_hash($password, PASSWORD_DEFAULT));
        }

        $userRepository->add($user, true);

        return new JsonResponse($user->toArray(), 200);
    }

    #[Route('/api/users/{id}', name: 'app_users_delete', methods: ['DELETE'])]
    public function delete(string $id, UserRepository $userRepository): JsonResponse
    {
        if (null === $user = $userRepository->find($id)) {
            return new JsonResponse(['message' => 'A user with this id does not exist.'], 404);
        }

        $userRepository->remove($user, true);

        return new JsonResponse(null, 204);
    }
}
