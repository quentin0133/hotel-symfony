<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\ResetPasswordRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordRequestInterface;
use SymfonyCasts\Bundle\ResetPassword\Persistence\Repository\ResetPasswordRequestRepositoryTrait;
use SymfonyCasts\Bundle\ResetPassword\Persistence\ResetPasswordRequestRepositoryInterface;

/**
 * Manages the persistence of password reset requests in the database.
 * Integrates with the SymfonyCasts ResetPassword bundle via the provided trait and interface.
 * @extends ServiceEntityRepository<ResetPasswordRequest>
 */
class ResetPasswordRequestRepository extends ServiceEntityRepository implements ResetPasswordRequestRepositoryInterface
{
    use ResetPasswordRequestRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResetPasswordRequest::class);
    }

    /**
     * Factory method required by the ResetPasswordBundle to instantiate a new reset request.
     * @param object             $user        The user requesting the reset (expected to be a Client instance)
     * @param \DateTimeInterface $expiresAt   The precise date and time when the reset token will expire
     * @param string             $selector    A non-secret unique string used to quickly fetch the request from the DB
     * @param string             $hashedToken The hashed version of the sensitive token used for verification
     * @return ResetPasswordRequestInterface The newly created password reset request entity
     */
    public function createResetPasswordRequest(object $user, \DateTimeInterface $expiresAt, string $selector, string $hashedToken): ResetPasswordRequestInterface
    {
        return new ResetPasswordRequest($user, $expiresAt, $selector, $hashedToken);
    }
}
