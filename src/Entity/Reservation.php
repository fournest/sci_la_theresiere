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

    

    #[ORM\Column(length: 255)]
    private ?string $dossierResa = null;

    #[ORM\Column]
    private ?bool $acompte = null;

    #[ORM\Column]
    private ?bool $caution = null;

   

    /**
     * @var Collection<int, Option>
     */
    #[ORM\ManyToMany(targetEntity: Option::class, inversedBy: 'reservations')]
    private Collection $options;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateResaFin = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    private ?Categorie $categorie = null;

    #[ORM\Column(length: 255)]
    private ?string $statut = null;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getCategorie(): ?Categorie
    {
        return $this->categorie;
    }

    public function setCategorie(?Categorie $categorie): static
    {
        $this->categorie = $categorie;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }
}
