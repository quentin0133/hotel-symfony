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

    public function findByClientAndNumReservationLikePaginated(?Client $client, string $numReservation, int $page, int $limit = 10): PaginationInterface
    {
        if ($client === null) {
            return $this->paginator->paginate([], $page, $limit);
        }

        $numReservation = trim($numReservation);
        $query = $this->createQueryBuilder('r');

        if (!empty($numReservation)) {
            $query = $this->createQueryBuilder('r')
                ->where('r.numReservation LIKE :codeHotel')
                ->setParameter('codeHotel', '%' . $numReservation . '%')
            ;
        }

        $query = $query
            ->andWhere('r.client = :client')
            ->setParameter('client', $client)
        ;

        return $this->paginator->paginate($query->getQuery(), $page, $limit);
    }

    public function findByNumReservationLikePaginated(string $numReservation, int $page, int $limit = 10): PaginationInterface
    {
        $numReservation = trim($numReservation);
        $query = $this->createQueryBuilder('r');
        if (!empty($numReservation)) {
            $query = $query
                ->where('r.numReservation LIKE :codeHotel')
                ->setParameter('codeHotel', '%' . $numReservation . '%')
            ;
        }
        return $this->paginator->paginate($query->getQuery(), $page, $limit);
    }
}
