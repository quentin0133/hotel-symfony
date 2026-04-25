<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reservation::class);
    }

    public function findByClient(?Client $client): array
    {
        if ($client === null) {
            return [];
        }

        return $this->createQueryBuilder('r')
            ->where('r.client = :client')
            ->setParameter('client', $client)
            ->getQuery()
            ->getResult()
        ;
    }
}
