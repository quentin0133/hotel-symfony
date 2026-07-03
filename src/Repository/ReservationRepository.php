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

    /**
     * Finds reservations for a specific client, optionally filtered by a partial reservation number.
     * @param Client|null $client         The client entity to filter by
     * @param string      $numReservation The partial or full reservation number to search for
     * @param int         $page           The current page number for pagination
     * @param int         $limit          The maximum number of items per page (default: 10)
     * @return PaginationInterface The paginated list of matching reservations or an empty pagination array if none is found
     */
    public function findByClientAndNumReservationLikePaginated(?Client $client, string $numReservation, int $page, int $limit = 10): PaginationInterface
    {
        if ($client === null) {
            return $this->paginator->paginate([], $page, $limit);
        }

        $numReservation = trim($numReservation);
        $query = $this->createQueryBuilder('r');

        if (!empty($numReservation)) {
            $query = $this->createQueryBuilder('r')
                ->where('r.numReservation LIKE :numReservation')
                ->setParameter('numReservation', '%' . $numReservation . '%')
            ;
        }

        $query = $query
            ->andWhere('r.client = :client')
            ->setParameter('client', $client)
        ;

        return $this->paginator->paginate($query->getQuery(), $page, $limit);
    }

    /**
     * Finds all reservations filtered by a partial reservation number across all clients.
     * @param string $numReservation The partial or full reservation number to search for
     * @param int    $page           The current page number for pagination
     * @param int    $limit          The maximum number of items per page (default: 10)
     * @return PaginationInterface The paginated list of matching reservations
     */
    public function findByNumReservationLikePaginated(string $numReservation, int $page, int $limit = 10): PaginationInterface
    {
        $numReservation = trim($numReservation);
        $query = $this->createQueryBuilder('r');
        if (!empty($numReservation)) {
            $query = $query
                ->where('r.numReservation LIKE :numReservation')
                ->setParameter('numReservation', '%' . $numReservation . '%')
            ;
        }
        return $this->paginator->paginate($query->getQuery(), $page, $limit);
    }
}
