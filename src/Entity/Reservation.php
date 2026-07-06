<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * The core transactional entity of the application.
 * Acts as the central junction binding Clients, Hotels, and Rooms within a specific timeframe.
 */
#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date de début est requise.')]
    private ?\DateTime $dateDebut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: 'La date de fin est requise.')]
    #[Assert\GreaterThan(propertyPath: 'dateDebut', message: 'La date de fin doit être postérieure à la date de début.')]
    private ?\DateTime $dateFin = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Hotel $hotel = null;

    /**
     * @var Collection<int, Chambre>
     */
    #[ORM\ManyToMany(targetEntity: Chambre::class, inversedBy: 'reservations')]
    private Collection $chambres;

    #[ORM\Column(length: 255)]
    private ?string $numReservation = null;

    /**
     *
     */
    public function __construct()
    {
        $this->chambres = new ArrayCollection();
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateDebut(): ?\DateTime
    {
        return $this->dateDebut;
    }

    /**
     * @param \DateTime|null $dateDebut
     * @return $this
     */
    public function setDateDebut(?\DateTime $dateDebut): static
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDateFin(): ?\DateTime
    {
        return $this->dateFin;
    }

    /**
     * @param \DateTime|null $dateFin
     * @return $this
     */
    public function setDateFin(?\DateTime $dateFin): static
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    /**
     * @param string|null $commentaire
     * @return $this
     */
    public function setCommentaire(?string $commentaire): static
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    /**
     * @return Client|null
     */
    public function getClient(): ?Client
    {
        return $this->client;
    }

    /**
     * @param Client|null $client
     * @return $this
     */
    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    /**
     * @return Hotel|null
     */
    public function getHotel(): ?Hotel
    {
        return $this->hotel;
    }

    /**
     * @param Hotel|null $hotel
     * @return $this
     */
    public function setHotel(?Hotel $hotel): static
    {
        $this->hotel = $hotel;

        return $this;
    }

    /**
     * @return Collection<int, Chambre>
     */
    public function getChambres(): Collection
    {
        return $this->chambres;
    }

    /**
     * Add a room from the collection
     * @param Chambre $chambre room added
     */
    public function addChambre(Chambre $chambre): static
    {
        if (!$this->chambres->contains($chambre)) {
            $this->chambres->add($chambre);
        }

        return $this;
    }

    /**
     * Remove a room from the collection
     * @param Chambre $chambre room removed
     */
    public function removeChambre(Chambre $chambre): static
    {
        $this->chambres->removeElement($chambre);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNumReservation(): ?string
    {
        return $this->numReservation;
    }

    /**
     * @param string $numReservation
     * @return $this
     */
    public function setNumReservation(string $numReservation): static
    {
        $this->numReservation = $numReservation;

        return $this;
    }
}
