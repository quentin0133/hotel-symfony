<?php

namespace App\Entity;

use App\Enum\ChambreTypeEnum;
use App\Repository\ChambreRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ChambreRepository::class)]
class Chambre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $codeChambre = null;

    #[ORM\Column(nullable: true)]
    private ?int $etage = null;

    #[ORM\Column]
    private ?int $nombreLit = null;

    #[ORM\ManyToOne(inversedBy: 'Chambre')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Hotel $hotel = null;

    #[ORM\Column(nullable: true, enumType: ChambreTypeEnum::class)]
    private ?ChambreTypeEnum $type = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodeChambre(): ?string
    {
        return $this->codeChambre;
    }

    public function setCodeChambre(string $codeChambre): static
    {
        $this->codeChambre = $codeChambre;

        return $this;
    }

    public function getEtage(): ?int
    {
        return $this->etage;
    }

    public function setEtage(?int $etage): static
    {
        $this->etage = $etage;

        return $this;
    }

    public function getNombreLit(): ?int
    {
        return $this->nombreLit;
    }

    public function setNombreLit(int $nombreLit): static
    {
        $this->nombreLit = $nombreLit;

        return $this;
    }

    public function getHotel(): ?Hotel
    {
        return $this->hotel;
    }

    public function setHotel(?Hotel $hotel): static
    {
        $this->hotel = $hotel;

        return $this;
    }

    public function getType(): ?ChambreTypeEnum
    {
        return $this->type;
    }

    public function setType(?ChambreTypeEnum $type): static
    {
        $this->type = $type;

        return $this;
    }
}
