<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry, private PaginatorInterface $paginator)
    {
        parent::__construct($registry, Client::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Client) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findByNameOrEmailLikePaginated(string $nameOrEmail, int $page, int $limit = 10): PaginationInterface
    {
        $nameOrEmail = trim($nameOrEmail);
        $query = $this->createQueryBuilder('c');
        if (!empty($nameOrEmail)) {
            $query = $query
                ->where('c.email LIKE :nameOrEmail')
                ->orWhere('c.nomClient LIKE :nameOrEmail')
                ->setParameter('nameOrEmail', '%' . $nameOrEmail . '%')
            ;
        }
        return $this->paginator->paginate($query->getQuery(), $page, $limit);
    }

    public function findOneByRole(string $role): ?Client
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.roles LIKE :role')
            ->setParameter('role', '%"' . $role . '"%')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
