<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findAllEnabled(): array
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.enabled = :enabled')
            ->setParameter('enabled', true)
            ->orderBy('e.dateEvenement', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
