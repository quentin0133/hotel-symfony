<?php

namespace App\Security\Voter;

use App\Entity\Reservation;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Grants or denies permissions for actions related to the Reservation entity
 * A client can only view, edit, or delete a reservation if they are its owner
 */
final class ReservationVoter extends Voter
{
    /** Permission to edit a reservation */
    public const EDIT = 'RESERVATION_EDIT';
    /** Permission to view a reservation */
    public const VIEW = 'RESERVATION_VIEW';
    /** Permission to delete a reservation */
    public const DELETE = 'RESERVATION_DELETE';

    /**
     * Determines if this voter should process the given attribute and subject
     * @param string $attribute The permission to evaluate (e.g., RESERVATION_EDIT)
     * @param mixed $subject The subject being secured (expected to be a Reservation instance)
     * @return bool True if the voter supports the attribute and subject, false otherwise
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof Reservation;
    }

    /**
     * Evaluates the business logic to grant or deny access to the Reservation
     * @param string $attribute The permission to evaluate
     * @param mixed $subject The subject being secured
     * @param TokenInterface $token The security token containing the current user
     * @param Vote|null $vote The current vote being evaluated
     * @return bool True to grant access, false to deny
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            $vote?->addReason('The user must be logged in to access this resource.');

            return false;
        }

        switch ($attribute) {
            case self::EDIT:
            case self::DELETE:
            case self::VIEW:
                return $subject->getClient()->getId() === $user->getId();
        }

        return false;
    }
}
