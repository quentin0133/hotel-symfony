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
     * Upgrades (rehashes) the user's password automatically over time to maintain security standards.
     * @param PasswordAuthenticatedUserInterface $user              The user whose password needs upgrading
     * @param string                             $newHashedPassword The newly generated hashed password
     * @throws UnsupportedUserException If the provided user is not an instance of Client
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

    /**
     * Finds clients by a partial match on either their name or email address.
     * @param string $nameOrEmail The partial string to search for in name or email fields
     * @param int    $page        The current page number for pagination
     * @param int    $limit       The maximum number of items per page (default: 10)
     * @return PaginationInterface The paginated list of clients matching the criteria
     */
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

    /**
     * Finds clients by a partial match on their role
     * @param string $role The security role to search for (e.g., 'ROLE_ADMIN')
     * @return Client|null The first matching client instance or null if none is found
     */
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
