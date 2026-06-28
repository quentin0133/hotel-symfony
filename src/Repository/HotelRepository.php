<?php

namespace App\Repository;

use App\Entity\Hotel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @extends ServiceEntityRepository<Hotel>
 */
class HotelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Hotel::class);
    }

    /**
     * Finds hotels
     * @param string $codeHotel
     * @param int $page
     * @param int $limit
     * @return PaginationInterface
     */
    public function findByCodeHotelLikePaginated(string $codeHotel, int $page, int $limit = 10): PaginationInterface
    {
        $codeHotel = trim($codeHotel);
        $query = $this->createQueryBuilder('r');
        if (!empty($codeHotel)) {
            $query = $query
                ->where('r.codeHotel LIKE :codeHotel')
                ->setParameter('codeHotel', '%' . $codeHotel . '%')
            ;
        }
        return $this->paginator->paginate($query->getQuery(), $page, $limit);
    }
}
