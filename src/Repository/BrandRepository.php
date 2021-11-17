<?php

namespace App\Repository;

use App\Entity\Brand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Brand|null find($id, $lockMode = null, $lockVersion = null)
 * @method Brand|null findOneBy(array $criteria, array $orderBy = null)
 * @method Brand[]    findAll()
 * @method Brand[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BrandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Brand::class);
    }

    public function findByQuery($searchValues)
    {
        $query = $this->createQueryBuilder('b');

        // search by name
        if (!empty($searchValues['name']))
        {
           $query->andWhere('b.name LIKE :name')
                ->setParameter('name', '%' . $searchValues['name'] . '%');
        }

        // search by client_id
        if (!empty($searchValues['client_id']))
        {
            $query->andWhere('b.client_id = :client_id')
                ->setParameter('client_id', $searchValues['client_id']);
        }

        // get all brand with client_id is null, example /?client_id=&name=example name
        if (isset($searchValues['client_id']) && empty($searchValues['client_id']))
        {
            $query->andWhere('b.client_id is null');
        }

        if (isset($searchValues['is_active']) && is_numeric($searchValues['is_active']))
        {
            $query->andWhere('b.client_id = :is_active')
                ->setParameter('is_active', $searchValues['is_active']);
        }

        return $query->getQuery()->getArrayResult();
    }

}
