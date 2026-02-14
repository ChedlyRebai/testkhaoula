<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function save(Post $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Post $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Récupérer tous les topics du forum
     */
    public function findAllOrderedByDate()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Rechercher des topics par titre ou contenu
     */
    public function searchPost(string $query)
    {
        return $this->createQueryBuilder('p')
            ->where('p.title LIKE :query')
            ->orWhere('p.content LIKE :query')
            ->setParameter('query', '%'.$query.'%')
            ->orderBy('p.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find posts using flexible filters, sorting and pagination.
     * Supported filters keys: q (search), tags, author, fromDate, toDate, sort (createdAt ASC|DESC), page, limit
     *
     * @param array $filters
     * @return Post[]
     */
    public function findByFilters(array $filters = []): array
    {
        $qb = $this->createQueryBuilder('p');

        if (!empty($filters['q'])) {
            $qb->andWhere('p.title LIKE :q OR p.content LIKE :q')
               ->setParameter('q', '%'.$filters['q'].'%');
        }

        if (!empty($filters['tags'])) {
            // allow passing comma-separated tags or single tag
            $tags = is_array($filters['tags']) ? $filters['tags'] : array_map('trim', explode(',', $filters['tags']));
            // simple approach: match any tag using LIKE on the tags field
            $orX = $qb->expr()->orX();
            foreach ($tags as $i => $tag) {
                $param = 'tag'.$i;
                $orX->add($qb->expr()->like('p.tags', ':'.$param));
                $qb->setParameter($param, '%'.$tag.'%');
            }
            $qb->andWhere($orX);
        }

        if (!empty($filters['author'])) {
            // allow partial match on author name
            $qb->andWhere('p.author LIKE :author')
               ->setParameter('author', '%'.$filters['author'].'%');
        }

        if (!empty($filters['fromDate'])) {
            $qb->andWhere('p.createdAt >= :fromDate')
               ->setParameter('fromDate', new \DateTimeImmutable($filters['fromDate']));
        }

        if (!empty($filters['toDate'])) {
            $qb->andWhere('p.createdAt <= :toDate')
               ->setParameter('toDate', new \DateTimeImmutable($filters['toDate']));
        }

        // Handle sorting: sortField + sortDir
        $allowedSortFields = ['createdAt', 'views', 'title', 'author', 'commentsCount'];
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

        // If sorting by commentsCount, we need to sort after fetching all results
        // because GROUP BY with pagination is complex in Doctrine
        $skipPaginationForCommentSort = ($sortField === 'commentsCount');
        
        // Add orderBy for non-commentCount sorts
        if (!$skipPaginationForCommentSort) {
            $qb->orderBy('p.'.$sortField, $sortDir);
        }
        
        if (!$skipPaginationForCommentSort) {
            // Apply pagination before fetching results for normal sorts
            $limit = isset($filters['limit']) ? (int) $filters['limit'] : 20;
            $page = isset($filters['page']) ? max(1, (int) $filters['page']) : 1;
            $qb->setFirstResult(($page - 1) * $limit)->setMaxResults($limit);
        }

        $results = $qb->getQuery()->getResult();

        // If sorting by commentsCount, need to sort manually after loading posts
        if ($skipPaginationForCommentSort) {
            // Create array with comment counts from loaded posts
            $resultsWithCounts = [];
            foreach ($results as $post) {
                $commentCount = count($post->getCommentaires());
                $resultsWithCounts[] = [
                    'post' => $post,
                    'count' => $commentCount
                ];
            }

            // Sort by comment count
            usort($resultsWithCounts, function($a, $b) use ($sortDir) {
                $cmp = $b['count'] <=> $a['count']; // Default: descending
                if ($sortDir === 'ASC') {
                    $cmp = $a['count'] <=> $b['count'];
                }
                return $cmp;
            });

            // Apply pagination after sorting
            $limit = isset($filters['limit']) ? (int) $filters['limit'] : 20;
            $page = isset($filters['page']) ? max(1, (int) $filters['page']) : 1;
            $offset = ($page - 1) * $limit;
            
            $paginatedResults = array_slice(
                array_map(fn($item) => $item['post'], $resultsWithCounts),
                $offset,
                $limit
            );
            
            return $paginatedResults;
        }

        return $results;
    }
}
