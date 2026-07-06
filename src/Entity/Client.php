<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Represents a registered client in the system.
 * Serves as the core User entity for Symfony's security and authentication system.
 */
#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Cet email est déjà utilisé.')]
class Client implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: "L'email est requis.")]
    #[Assert\Email(message: "L'adresse email '{{ value }}' n'est pas valide.")]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le code client est requis.')]
    #[Assert\Length(max: 255, maxMessage: 'Le code client ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $codeClient = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $nomClient = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $adrClient = null;

    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'client', orphanRemoval: true)]
    private Collection $reservations;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $telClient = null;

    /**
     *
     */
    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return $this
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user in the security token.
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Retrieves the user roles, guaranteeing a baseline access level.
     * @return list<string> The roles granted to the user
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_CLIENT
        $roles[] = 'ROLE_CLIENT';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0".self::class."\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    /**
     * @return string|null
     */
    public function getCodeClient(): ?string
    {
        return $this->codeClient;
    }

    /**
     * @param string $codeClient
     * @return $this
     */
    public function setCodeClient(string $codeClient): static
    {
        $this->codeClient = $codeClient;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNomClient(): ?string
    {
        return $this->nomClient;
    }

    /**
     * @param string $nomClient
     * @return $this
     */
    public function setNomClient(string $nomClient): static
    {
        $this->nomClient = $nomClient;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAdrClient(): ?string
    {
        return $this->adrClient;
    }

    /**
     * @param string|null $adrClient
     * @return $this
     */
    public function setAdrClient(?string $adrClient): static
    {
        $this->adrClient = $adrClient;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    /**
     * @return string|null
     */
    public function getTelClient(): ?string
    {
        return $this->telClient;
    }

    /**
     * @param string|null $telClient
     * @return $this
     */
    public function setTelClient(?string $telClient): static
    {
        $this->telClient = $telClient;

        return $this;
    }

    /**
     * Safely adds a reservation to the collection while maintaining the bidirectional relationship.
     */
    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setClient($this);
        }

        return $this;
    }

    /**
     * Safely removes a reservation and nullifies the owning side to prevent database inconsistencies.
     */
    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getClient() === $this) {
                $reservation->setClient(null);
            }
        }

        return $this;
    }
}
