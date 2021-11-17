<?php

namespace App\Controller\Api\v1;

use App\Entity\Brand;
use App\Repository\BrandRepository;
use App\Service\ApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BrandController extends GeneralController
{
    /**
     * @param Request $request
     * @param BrandRepository $brand_repository
     * @param ApiService $api_service
     * @return JsonResponse
     * @Route("/brand", name="brands", methods={"GET"})
     */
    public function getBrands(Request $request, BrandRepository $brand_repository, ApiService $api_service): JsonResponse
    {
        $brand = $brand_repository->findByQuery($request->query->all());
        return $this->json($brand);
    }

    /**
     * @param BrandRepository $brand_repository
     * @param $id
     * @return JsonResponse
     * @Route("/brand/{id}", name="brand", methods={"GET"})
     */
    public function getBrand(BrandRepository $brand_repository, $id): JsonResponse
    {
        $brand = $brand_repository->find($id);

        if (!$brand) return $this->json('Brand not found', Response::HTTP_NOT_FOUND);

        return $this->json($brand);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param ApiService $api_service
     * @return JsonResponse
     * @Route("/brand", name="add_brand", methods={"POST"})
     */
    public function addBrand(Request $request, EntityManagerInterface $entityManager, ApiService $api_service): JsonResponse
    {
        $validate_rules = [
            ['name' => 'name', 'required' => true]
        ];
        try {
            $data = $api_service->formatRequest($request);
            $this->validateParams($data, $validate_rules);
            $brand = new Brand();
            $brand->setName($data->get('name'));
            $brand->setIsActive($data->get('is_active') ?? Brand::STATUS_INACTIVE);
            $brand->setClientId($data->get('client_id'));
            $entityManager->persist($brand);
            $entityManager->flush();

            return $this->json([
                'message' => 'Brand has been added successfully',
                'entity' => [
                    'id' => $brand->getId(),
                    'name' => $brand->getName()
                ]
            ]);

        } catch (\Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param BrandRepository $brand_repository
     * @param ApiService $api_service
     * @return JsonResponse
     * @Route("/brand", name="update_brand", methods={"PUT"})
     */
    public function updateBrand(Request $request, EntityManagerInterface $entityManager, BrandRepository $brand_repository, ApiService $api_service): JsonResponse
    {
        $validate_rules = [
            ['name' => 'name', 'required' => true],
            ['name' => 'id', 'required' => true]
        ];
        try {
            $data = $api_service->formatRequest($request);
            $this->validateParams($data, $validate_rules);

            $brand = $brand_repository->find($data->get('id'));
            if (!$brand) $this->addError('Brand with id ' . $data->get('id') . ' not found', self::ERROR_NOT_FOUND, true);

            $brand->setName($data->get('name'));
            $brand->setIsActive($data->get('is_active'));
            $brand->setClientId($data->get('client_id'));
            $entityManager->flush();

            return $this->json('Brand has been updated successfully');

        } catch (\Exception $e) {
            return $this->json($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @param EntityManagerInterface $entityManager
     * @param BrandRepository $brand_repository
     * @param $id
     * @return JsonResponse
     * @Route("/brand/{id}", name="delete_brand", methods={"DELETE"})
     */
    public function deleteBrand(EntityManagerInterface $entityManager, BrandRepository $brand_repository, $id): JsonResponse
    {
        $brand = $brand_repository->find($id);
        if (!$brand) return $this->json('Brand not found', Response::HTTP_NOT_FOUND);

        $entityManager->remove($brand);
        $entityManager->flush();
        return $this->json('Brand has been delete successfully');
    }
}
