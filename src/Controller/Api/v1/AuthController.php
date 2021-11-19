<?php

namespace App\Controller\Api\v1;

use App\Entity\Token;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\ApiService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthController extends GeneralController
{
    /**
     * @param Request $request
     * @param ApiService $apiService
     * @param UserRepository $user_repository
     * @param UserPasswordHasherInterface $password_hasher
     * @param EntityManagerInterface $entity_manager
     * @return JsonResponse
     * @Route("/auth/login", name="auth_login", methods={"POST"})
     */
    public function login(Request $request,
                          ApiService $apiService,
                          UserRepository $user_repository,
                          UserPasswordHasherInterface $password_hasher,
                          EntityManagerInterface $entity_manager
                        ): JsonResponse
    {
        $data = $apiService->formatRequest($request);
        $user = $user_repository->findOneBy(['username' => $data->get('username')]);
        if (empty($user) || !$user->validatePassword($password_hasher, $data->get('password') ?? ''))
            return $this->json('Failed to authenticate user', Response::HTTP_FORBIDDEN);
        try {
            $new_token = $user->generateNewToken($entity_manager);

            return $this->json([
                'token' => $new_token->getToken()
            ], Response::HTTP_OK);

        } catch (\Exception $e)
        {
            return $this->json('An unknown error has occurred.', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * @param Request $request
     * @param ApiService $apiService
     * @param UserRepository $user_repository
     * @param UserPasswordHasherInterface $password_hasher
     * @param EntityManager $entity_manager
     * @param ValidatorInterface $validator
     * @return JsonResponse
     * @Route ("/auth/register", name="auth_register", methods={"POST"})
     */
    public function register(Request $request,
                             ApiService $apiService,
                             UserRepository $user_repository,
                             UserPasswordHasherInterface $password_hasher,
                             EntityManagerInterface $entity_manager,
                             ValidatorInterface $validator
                            ): JsonResponse
    {
        // Format request
        $data = $apiService->formatRequest($request);

        // Search user
        // Validate password by pattern
        $user = $user_repository->findOneBy(['username' => $data->get('username')]);
        $password = $data->get('password');
        if ($user) return $this->json('This user already exists', Response::HTTP_FORBIDDEN);
        if (!preg_match('~(?=.*[0-9])(?=.*[!@#$%^&*])(?=.*[a-zA-Z])([a-zA-Z0-9!@#$%^&*]){8,}~', $password))
        {
            $this->addError('Password must contain at least 8 characters, at least 1 digit and one special character from the list [!@#$%^&*]', self::ERROR_TEMPLATE);
            return $this->json('You have errors', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        // Register new User
        $user = new User();
        $user->setUsername($data->get('username'));
        $hashedPassword = $password_hasher->hashPassword(
            $user,
            $data->get('password')
        );
        $user->setPassword($hashedPassword);

        // Validate
        $errors = $validator->validate($user);
        if (count($errors) > 0)
        {
            #dd((string)$errors);
            return $this->json('You have errors with input data', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Save and generate new token
        try {
            $entity_manager->persist($user);
            $entity_manager->flush();

            $new_token = $user->generateNewToken($entity_manager);

            return $this->json([
                'message' => 'Registration successful',
                'token' => $new_token->getToken()
            ], Response::HTTP_OK);
        } catch (\Exception $e)
        {
            return $this->json('An unknown error has occurred.', Response::HTTP_INTERNAL_SERVER_ERROR);

        }

    }
}