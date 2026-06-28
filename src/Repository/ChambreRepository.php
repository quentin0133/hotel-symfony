<?php

namespace App\Repository;

use App\Entity\Chambre;
use App\Entity\Reservation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Chambre>
 */
class ChambreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Chambre::class);
    }

    /**
     * Finds rooms by a partial match on their code
     * @param string $codeChambre The partial or full room code to search for
     * @param int $page The current page number for pagination
     * @param int $limit The maximum number of items per page (default: 10)
     * @return PaginationInterface The paginated list of rooms matching the code
     */
    public function findByCodeChambreLikePaginated(string $codeChambre, int $page, int $limit = 10): PaginationInterface
    {
        $codeChambre = trim($codeChambre);
        $query = $this->createQueryBuilder('c');
        if (!empty($codeChambre)) {
            $query = $query
                ->where('c.codeChambre LIKE :codeChambre')
                ->setParameter('codeChambre', '%' . $codeChambre . '%');
        }
        return $this->paginator->paginate($query->getQuery(), $page, $limit);
    }


    /**
     * Finds rooms that have no overlapping reservations for the requested period
     * @param \DateTime $dateDebut The start date of the requested period
     * @param \DateTime $dateFin The end date of the requested period
     * @param int $page The current page number for pagination
     * @param int $limit The maximum number of items per page (default: 10)
     * @return PaginationInterface The paginated list of available rooms
     */
    public function findAvailableBetweenDates(\DateTime $dateDebut, \DateTime $dateFin, int $page, int $limit = 10): PaginationInterface
    {
        $query = $this->createQueryBuilder('c')
            ->leftJoin('c.hotel', 'h')
            ->addSelect('h')
            ->where('c NOT IN (
                SELECT c2 FROM ' . Reservation::class . ' r JOIN r.chambres c2
                WHERE r.dateDebut < :dateFin AND r.dateFin > :dateDebut
            )')
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->orderBy('h.nomHotel')
            ->addOrderBy('c.codeChambre')
            ->getQuery();

        return $this->paginator->paginate($query, $page, $limit);
    }
}
