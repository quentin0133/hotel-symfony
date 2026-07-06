<?php

namespace App\Entity;

use App\Enum\ChambreTypeEnum;
use App\Repository\ChambreRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Represents a hotel room within the domain.
 * Centralizes data persistence mapping and business validation constraints.
 */
#[ORM\Entity(repositoryClass: ChambreRepository::class)]
class Chambre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le code chambre est requis.')]
    #[Assert\Length(max: 255, maxMessage: 'Le code chambre ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $codeChambre = null;

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: 'L\'étage doit être un nombre positif ou zéro.')]
    private ?int $etage = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le nombre de lits est requis.')]
    #[Assert\Positive(message: 'Le nombre de lits doit être au moins 1.')]
    private ?int $nombreLit = null;

    #[ORM\ManyToOne(inversedBy: 'chambres')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Hotel $hotel = null;

    #[ORM\Column(nullable: true, enumType: ChambreTypeEnum::class)]
    private ?ChambreTypeEnum $type = null;

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
    public function getCodeChambre(): ?string
    {
        return $this->codeChambre;
    }

    /**
     * @param string $codeChambre
     * @return $this
     */
    public function setCodeChambre(string $codeChambre): static
    {
        $this->codeChambre = $codeChambre;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getEtage(): ?int
    {
        return $this->etage;
    }

    /**
     * @param int|null $etage
     * @return $this
     */
    public function setEtage(?int $etage): static
    {
        $this->etage = $etage;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getNombreLit(): ?int
    {
        return $this->nombreLit;
    }

    /**
     * @param int $nombreLit
     * @return $this
     */
    public function setNombreLit(int $nombreLit): static
    {
        $this->nombreLit = $nombreLit;

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
     * @return ChambreTypeEnum|null
     */
    public function getType(): ?ChambreTypeEnum
    {
        return $this->type;
    }

    /**
     * @param ChambreTypeEnum|null $type
     * @return $this
     */
    public function setType(?ChambreTypeEnum $type): static
    {
        $this->type = $type;

        return $this;
    }
}
