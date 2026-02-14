<?php

namespace App\Repository;

use App\Entity\Tache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tache>
 */
class TacheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tache::class);
    }

    /**
     * Find taches with advanced search, sort and filter (with limit for performance)
     */
    public function search(
        ?string $search = '',
        ?int $projetId = null,
        ?string $statut = null,
        ?int $priorite = null,
        string $sort = 'date',
        string $order = 'DESC',
        bool $enabledOnly = true,
        int $limit = 100,
        int $offset = 0
    ): array {
        $qb = $this->createQueryBuilder('t')
            ->leftJoin('t.projet', 'p')
            ->addSelect('p');

        // Filter by enabled status
        if ($enabledOnly) {
            $qb->andWhere('t.enabled = :enabled')
               ->setParameter('enabled', true);
        }

        // Search by titre or description
        if (!empty($search)) {
            $qb->andWhere('t.titre LIKE :search OR t.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        // Filter by projet
        if ($projetId !== null) {
            $qb->andWhere('t.projet = :projet')
               ->setParameter('projet', $projetId);
        }

        // Filter by statut
        if ($statut !== null) {
            $qb->andWhere('t.statut = :statut')
               ->setParameter('statut', $statut);
        }

        // Filter by priorite
        if ($priorite !== null) {
            $qb->andWhere('t.priorite = :priorite')
               ->setParameter('priorite', $priorite);
        }

        // Sort by field
        $validSortFields = ['titre', 'date', 'statut', 'priorite', 'id'];
        $sortField = in_array($sort, $validSortFields) ? $sort : 'date';
        $validOrder = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

        if ($sortField === 'date') {
            $qb->orderBy('t.dateCreation', $validOrder);
        } else {
            $qb->orderBy('t.' . $sortField, $validOrder);
        }

        // Add limit and offset for pagination
        $qb->setMaxResults($limit)->setFirstResult($offset);

        return $qb->getQuery()->getResult();
    }

    /**
     * Count total by search criteria
     */
    public function countBySearch(
        ?string $search = '',
        ?int $projetId = null,
        ?string $statut = null,
        ?int $priorite = null,
        bool $enabledOnly = true
    ): int {
        $qb = $this->createQueryBuilder('t');

        if ($enabledOnly) {
            $qb->andWhere('t.enabled = :enabled')
               ->setParameter('enabled', true);
        }

        if (!empty($search)) {
            $qb->andWhere('t.titre LIKE :search OR t.description LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($projetId !== null) {
            $qb->andWhere('t.projet = :projet')
               ->setParameter('projet', $projetId);
        }

        if ($statut !== null) {
            $qb->andWhere('t.statut = :statut')
               ->setParameter('statut', $statut);
        }

        if ($priorite !== null) {
            $qb->andWhere('t.priorite = :priorite')
               ->setParameter('priorite', $priorite);
        }

        return (int) $qb->select('COUNT(t.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
