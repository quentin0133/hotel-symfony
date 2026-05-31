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
     * Retourne les chambres disponibles sur la période, paginées.
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
