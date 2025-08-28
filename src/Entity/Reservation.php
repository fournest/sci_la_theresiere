<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateResaDebut = null;

    #[ORM\Column]
    private ?int $utilisateurId = null;

    #[ORM\Column(length: 255)]
    private ?string $dossierResa = null;

    #[ORM\Column]
    private ?bool $acompte = null;

    #[ORM\Column]
    private ?bool $caution = null;

    #[ORM\Column]
    private ?int $categorieId = null;

    /**
     * @var Collection<int, Option>
     */
    #[ORM\ManyToMany(targetEntity: Option::class, inversedBy: 'reservations')]
    private Collection $options;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateResaFin = null;

    public function __construct()
    {
        $this->options = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateResaDebut(): ?\DateTime
    {
        return $this->dateResaDebut;
    }

    public function setDateResaDebut(\DateTime $dateResaDebut): static
    {
        $this->dateResaDebut = $dateResaDebut;

        return $this;
    }

    public function getUtilisateurId(): ?int
    {
        return $this->utilisateurId;
    }

    public function setUtilisateurId(int $utilisateurId): static
    {
        $this->utilisateurId = $utilisateurId;

        return $this;
    }

    public function getDossierResa(): ?string
    {
        return $this->dossierResa;
    }

    public function setDossierResa(string $dossierResa): static
    {
        $this->dossierResa = $dossierResa;

        return $this;
    }

    public function isAcompte(): ?bool
    {
        return $this->acompte;
    }

    public function setAcompte(bool $acompte): static
    {
        $this->acompte = $acompte;

        return $this;
    }

    public function isCaution(): ?bool
    {
        return $this->caution;
    }

    public function setCaution(bool $caution): static
    {
        $this->caution = $caution;

        return $this;
    }

    public function getCategorieId(): ?int
    {
        return $this->categorieId;
    }

    public function setCategorieId(int $categorieId): static
    {
        $this->categorieId = $categorieId;

        return $this;
    }

    /**
     * @return Collection<int, Option>
     */
    public function getOptions(): Collection
    {
        return $this->options;
    }

    public function addOption(Option $option): static
    {
        if (!$this->options->contains($option)) {
            $this->options->add($option);
        }

        return $this;
    }

    public function removeOption(Option $option): static
    {
        $this->options->removeElement($option);

        return $this;
    }

    public function getDateResaFin(): ?\DateTime
    {
        return $this->dateResaFin;
    }

    public function setDateResaFin(\DateTime $dateResaFin): static
    {
        $this->dateResaFin = $dateResaFin;

        return $this;
    }
}
