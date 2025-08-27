<?php

namespace App\Entity;

use App\Repository\VisiteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: VisiteRepository::class)]
class Visite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateVisite = null;

    #[ORM\Column]
    private ?int $utilisateurId = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $dateResaSouhaite = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateVisite(): ?\DateTime
    {
        return $this->dateVisite;
    }

    public function setDateVisite(\DateTime $dateVisite): static
    {
        $this->dateVisite = $dateVisite;

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

    public function getDateResaSouhaite(): ?\DateTime
    {
        return $this->dateResaSouhaite;
    }

    public function setDateResaSouhaite(\DateTime $dateResaSouhaite): static
    {
        $this->dateResaSouhaite = $dateResaSouhaite;

        return $this;
    }
}
