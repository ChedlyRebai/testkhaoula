<?php

namespace App\Repository;

use App\Entity\Projet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Projet>
 */
class ProjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Projet::class);
    }

    /**
     * Find projets with search, sort and filter (with limit for performance)
     */
    public function search(
        ?string $search = '', 
        string $sort = 'date', 
        string $order = 'DESC', 
        bool $enabledOnly = true,
        int $limit = 100,
        int $offset = 0,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {
        $qb = $this->createQueryBuilder('p');

        // Filter by enabled status
        if ($enabledOnly) {
            $qb->andWhere('p.enabled = :enabled')
               ->setParameter('enabled', true);
        }

        // Search by nom or description
        if (!empty($search)) {
            $qb->andWhere('p.nom LIKE :search OR p.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // Filter by date range
        if ($dateFrom) {
            $dateFromObj = \DateTime::createFromFormat('Y-m-d', $dateFrom);
            if ($dateFromObj) {
                $qb->andWhere('p.dateCreation >= :dateFrom')
                   ->setParameter('dateFrom', $dateFromObj);
            }
        }
        if ($dateTo) {
            $dateToObj = \DateTime::createFromFormat('Y-m-d', $dateTo);
            if ($dateToObj) {
                $dateToObj->modify('+1 day');
                $qb->andWhere('p.dateCreation < :dateTo')
                   ->setParameter('dateTo', $dateToObj);
            }
        }

        // Sort by field
        $validSortFields = ['nom', 'date', 'id'];
        $sortField = in_array($sort, $validSortFields) ? $sort : 'date';
        $validOrder = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        if ($sortField === 'date') {
            $qb->orderBy('p.dateCreation', $validOrder);
        } else {
            $qb->orderBy('p.' . $sortField, $validOrder);
        }

        // Add limit and offset for pagination
        $qb->setMaxResults($limit)->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    /**
     * Count total by search criteria
     */
    public function countBySearch(?string $search = '', bool $enabledOnly = true): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('COUNT(p.id)');

        if ($enabledOnly) {
            $qb->andWhere('p.enabled = :enabled')
               ->setParameter('enabled', true);
        }

        if (!empty($search)) {
            $qb->andWhere('p.nom LIKE :search OR p.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        return (int) $qb->getQuery()->getSingleScalarResult();
    }
}
