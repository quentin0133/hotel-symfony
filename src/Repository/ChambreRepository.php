<?php

namespace App\Repository;

use App\Entity\Chambre;
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
        $query = $this->createQueryBuilder('c');
        if (!empty($codeChambre)) {
            $query = $query
                ->where('c.codeChambre LIKE :codeChambre')
                ->setParameter('codeChambre', '%' . $codeChambre . '%');
        }
        return $this->paginator->paginate($query->getQuery(), $page, $limit);
    }
}
