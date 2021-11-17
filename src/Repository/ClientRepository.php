<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Client|null find($id, $lockMode = null, $lockVersion = null)
 * @method Client|null findOneBy(array $criteria, array $orderBy = null)
 * @method Client[]    findAll()
 * @method Client[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Client::class);
    }

    public function findByQuery($searchValues)
    {
        $query = $this->createQueryBuilder('c');

        // search by name
        if (!empty($searchValues['name']))
        {
            $query->andWhere('c.name LIKE :name')
                ->setParameter('name', '%' . $searchValues['name'] . '%');
        }
        if (isset($searchValues['is_active']) && is_numeric($searchValues['is_active']))
        {
            $query->andWhere('b.client_id = :is_active')
                ->setParameter('is_active', $searchValues['is_active']);
        }

        return $query->getQuery()->getArrayResult();
    }
}
