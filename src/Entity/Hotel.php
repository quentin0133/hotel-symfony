<?php

namespace App\Entity;

use App\Repository\HotelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Represents an hotel which will have room that can be reserved by customers
 */
#[ORM\Entity(repositoryClass: HotelRepository::class)]
class Hotel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le code hôtel est requis.')]
    #[Assert\Length(max: 255, maxMessage: 'Le code hôtel ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $codeHotel = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le nom de l\'hôtel est requis.')]
    #[Assert\Length(max: 255, maxMessage: 'Le nom ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $nomHotel = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'adresse de l\'hôtel est requise.')]
    #[Assert\Length(max: 255, maxMessage: 'L\'adresse ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $adresseHotel = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $categorieHotel = null;

    /**
     * @var Collection<int, Chambre>
     */
    #[ORM\OneToMany(targetEntity: Chambre::class, mappedBy: 'hotel', cascade: ['remove'], orphanRemoval: true)]
    private Collection $chambres;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'hotel', cascade: ['remove'], orphanRemoval: true)]
    private Collection $reservations;

    /**
     *
     */
    public function __construct()
    {
        $this->chambres = new ArrayCollection();
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
    public function getCodeHotel(): ?string
    {
        return $this->codeHotel;
    }

    /**
     * @param string $codeHotel
     * @return $this
     */
    public function setCodeHotel(string $codeHotel): static
    {
        $this->codeHotel = $codeHotel;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getNomHotel(): ?string
    {
        return $this->nomHotel;
    }

    /**
     * @param string $nomHotel
     * @return $this
     */
    public function setNomHotel(string $nomHotel): static
    {
        $this->nomHotel = $nomHotel;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getAdresseHotel(): ?string
    {
        return $this->adresseHotel;
    }

    /**
     * @param string $adresseHotel
     * @return $this
     */
    public function setAdresseHotel(string $adresseHotel): static
    {
        $this->adresseHotel = $adresseHotel;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getCategorieHotel(): ?string
    {
        return $this->categorieHotel;
    }

    /**
     * @param string|null $categorieHotel
     * @return $this
     */
    public function setCategorieHotel(?string $categorieHotel): static
    {
        $this->categorieHotel = $categorieHotel;

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
     * Add a room to the collection
     * @param Chambre $chambre room added
     */
    public function addChambre(Chambre $chambre): static
    {
        if (!$this->chambres->contains($chambre)) {
            $this->chambres->add($chambre);
            $chambre->setHotel($this);
        }

        return $this;
    }

    /**
     * Remove a room from the collection
     * @param Chambre $chambre room removed
     */
    public function removeChambre(Chambre $chambre): static
    {
        if ($this->chambres->removeElement($chambre)) {
            // set the owning side to null (unless already changed)
            if ($chambre->getHotel() === $this) {
                $chambre->setHotel(null);
            }
        }

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
     * Add a reservation from the collection
     * @param Reservation $reservation reservation added
     */
    public function addReservation(Reservation $reservation): static
    {
        if (!$this->reservations->contains($reservation)) {
            $this->reservations->add($reservation);
            $reservation->setHotel($this);
        }

        return $this;
    }

    /**
     * Remove a reservation from the collection
     * @param Reservation $reservation reservation removed
     */
    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getHotel() === $this) {
                $reservation->setHotel(null);
            }
        }

        return $this;
    }
}
