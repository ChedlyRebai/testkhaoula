<?php

namespace App\Repository;

use App\Entity\Commentaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Commentaire>
 */
class CommentaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Commentaire::class);
    }

    public function save(Commentaire $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Commentaire $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Récupérer tous les commentaires d'un post
     */
    public function findByPostOrderedByDate($postId)
    {
        return $this->createQueryBuilder('c')
            ->where('c.post = :postId')
            ->setParameter('postId', $postId)
            ->orderBy('c.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find comments by flexible filters (postId optional)
     * Supported: postId, author, q, fromDate, toDate, sort, page, limit
     */
    public function findByFilters(array $filters = []): array
    {
        $qb = $this->createQueryBuilder('c');

        if (!empty($filters['postId'])) {
            $qb->andWhere('c.post = :postId')->setParameter('postId', $filters['postId']);
        }

        if (!empty($filters['author'])) {
            $qb->andWhere('c.author = :author')->setParameter('author', $filters['author']);
        }

        if (!empty($filters['q'])) {
            $qb->andWhere('c.content LIKE :q')->setParameter('q', '%'.$filters['q'].'%');
        }

        if (!empty($filters['fromDate'])) {
            $qb->andWhere('c.createdAt >= :fromDate')->setParameter('fromDate', new \DateTimeImmutable($filters['fromDate']));
        }

        if (!empty($filters['toDate'])) {
            $qb->andWhere('c.createdAt <= :toDate')->setParameter('toDate', new \DateTimeImmutable($filters['toDate']));
        }

        // Handle sorting: sortField + sortDir
        $allowedSortFields = ['createdAt', 'author'];
        $sortField = $filters['sortField'] ?? 'createdAt';
        if (!in_array($sortField, $allowedSortFields, true)) {
            $sortField = 'createdAt';
        }

        $sortDir = strtoupper($filters['sortDir'] ?? 'DESC');
        if (!in_array($sortDir, ['ASC', 'DESC'], true)) {
            $sortDir = 'DESC';
        }

        // Backward compatibility: legacy 'sort' parameter for direction only
        if (!empty($filters['sort']) && empty($filters['sortDir'])) {
            $legacySort = strtoupper($filters['sort']);
            if (in_array($legacySort, ['ASC', 'DESC'], true)) {
                $sortDir = $legacySort;
            }
        }

        $qb->orderBy('c.'.$sortField, $sortDir);

        $limit = isset($filters['limit']) ? (int) $filters['limit'] : 50;
        $page = isset($filters['page']) ? max(1, (int) $filters['page']) : 1;
        $qb->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    /**
     * Return an array of post IDs which have comments by the given author (exact match)
     * @return int[]
     */
    public function findPostIdsByAuthor(string $author): array
    {
        $rows = $this->createQueryBuilder('c')
            ->select('IDENTITY(c.post) as postId')
            ->where('c.author LIKE :author')
            ->setParameter('author', '%'.$author.'%')
            ->groupBy('c.post')
            ->getQuery()
            ->getScalarResult();

        return array_map(function($r){ return (int)$r['postId']; }, $rows);
    }
}
