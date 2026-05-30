<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Reservation>
 */
class ReservationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
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

    public function findByNumReservationLikePaginated(string $numReservation, int $page, int $limit = 10): PaginationInterface
    {
        $query = $this->createQueryBuilder('r');
        if (!empty($numReservation)) {
            $query = $query
                ->where('r.numReservation LIKE :numReservation')
                ->setParameter('numReservation', '%' . $numReservation . '%');
        }
        return $this->paginator->paginate($query->getQuery(), $page, $limit);
    }
}
