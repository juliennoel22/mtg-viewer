<?php

namespace App\Repository;

use App\Entity\Card;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Card>
 *
 * @method Card|null find($id, $lockMode = null, $lockVersion = null)
 * @method Card|null findOneBy(array $criteria, array $orderBy = null)
 * @method Card[]    findAll()
 * @method Card[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Card::class);
    }

    public function getAllUuids(): array
    {
        $result =  $this->createQueryBuilder('c')
            ->select('c.uuid')
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_ARRAY)
        ;
        return array_column($result, 'uuid');
    }

    /**
     * @return string[]
     */
    public function findAllUniqueSetCodes(): array
    {
        return $this->createQueryBuilder('c')
            ->select('DISTINCT c.setCode')
            ->where('c.setCode IS NOT NULL')
            ->orderBy('c.setCode', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    /**
     * @return Card[]
     */
    public function searchByName(string $query, ?string $setCode = null, ?int $artistId = null, int $limit = 20): array
    {
        $qb = $this->createQueryBuilder('c')
            ->where('c.name LIKE :query')
            ->setParameter('query', '%' . $query . '%');

        if ($setCode) {
            $qb->andWhere('c.setCode = :set')
               ->setParameter('set', $setCode);
        }

        if ($artistId) {
            $qb->andWhere('c.artist = :artistId')
               ->setParameter('artistId', $artistId);
        }

        return $qb->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
