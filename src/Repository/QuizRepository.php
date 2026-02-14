<?php

namespace App\Repository;

use App\Entity\Quiz;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Quiz>
 */
class QuizRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quiz::class);
    }

    /**
     * Exemple de méthode personnalisée :
     * Récupérer les quiz actifs uniquement.
     *
     * @return Quiz[]
     */
    public function findActive(): array
    {
        return $this->createQueryBuilder('q')
            ->andWhere('q.actif = :val')
            ->setParameter('val', true)
            ->orderBy('q.date_creation', 'DESC')
            ->getQuery()
            ->getResult();
    }
}


