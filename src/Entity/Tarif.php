<?php

namespace App\Entity;

use App\Repository\TarifRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TarifRepository::class)]
class Tarif
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $prixReservation = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $prixOption = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrixReservation(): ?string
    {
        return $this->prixReservation;
    }

    public function setPrixReservation(string $prixReservation): static
    {
        $this->prixReservation = $prixReservation;

        return $this;
    }

    public function getPrixOption(): ?string
    {
        return $this->prixOption;
    }

    public function setPrixOption(string $prixOption): static
    {
        $this->prixOption = $prixOption;

        return $this;
    }
}
