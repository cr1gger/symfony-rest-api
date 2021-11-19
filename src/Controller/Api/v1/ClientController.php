<?php

namespace App\Controller\Api\v1;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Service\ApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class ClientController extends SecurityController
{
    /**
     *
     * @param Request $request
     * @param ClientRepository $client_repository
     * @return JsonResponse
     * @Route("/client", name="clients", methods={"GET"})
     */
    public function getClients(Request $request, ClientRepository $client_repository): JsonResponse
    {
        $data = $client_repository->findByQuery($request->query->all());
        return $this->json($data);
    }

    /**
     * @param ClientRepository $client_repository
     * @param $id
     * @return JsonResponse
     * @Route("/client/{id}", name="client", methods={"GET"})
     */
    public function getClient(ClientRepository $client_repository, $id): JsonResponse
    {
        $data = $client_repository->find($id);

        if (!$data) return $this->json('Client not found', Response::HTTP_NOT_FOUND);

        return $this->json($data);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param ApiService $api_service
     * @return JsonResponse
     * @Route("/client", name="add_client", methods={"POST"})
     */
    public function addClient(Request $request, EntityManagerInterface $entityManager, ApiService $api_service): JsonResponse
    {
        $validate_rules = [
            ['name' => 'name', 'required' => true]
        ];
        try {
            $data = $api_service->formatRequest($request);

            $this->validateParams($data, $validate_rules);

            $client = new Client();
            $client->setName($data->get('name'));
            $client->setIsActive($data->get('is_active') ?? Client::STATUS_INACTIVE);
            $entityManager->persist($client);
            $entityManager->flush();

            return $this->json([
                'message' => 'Client has been added successfully',
                'entity' => [
                    'id' => $client->getId(),
                    'name' => $client->getName()
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param ClientRepository $client_repository
     * @param ApiService $api_service
     * @return JsonResponse
     * @Route("/client", name="update_client", methods={"PUT"})
     */
    public function updateClient(Request $request, EntityManagerInterface $entityManager, ClientRepository $client_repository, ApiService $api_service): JsonResponse
    {
        $validate_rules = [
            ['name' => 'id', 'required' => true],
            ['name' => 'name', 'required' => true],
            ['name' => 'is_active', 'required' => true]
        ];
        try {
            $data = $api_service->formatRequest($request);

            $this->validateParams($data, $validate_rules);
            $client = $client_repository->find($data->get('id'));
            if (!$client) throw new \Exception('Client not found');

            $client->setName($data->get('name'));
            $client->setIsActive($data->get('is_active'));
            $entityManager->flush();

            return $this->json('Client has been updated successfully');

        } catch (\Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param ClientRepository $client_repository
     * @param ApiService $api_service
     * @param $id
     * @return JsonResponse
     * @Route("/client/{id}", name="delete_client", methods={"DELETE"})
     */
    public function deleteClient(EntityManagerInterface $entityManager, ClientRepository $client_repository, ApiService $api_service, $id): JsonResponse
    {
        $data = $client_repository->find($id);
        if (!$data) return $this->json('Client not found', Response::HTTP_NOT_FOUND);

        $entityManager->remove($data);
        $entityManager->flush();
        return $this->json('Client has been delete successfully');
    }
}
