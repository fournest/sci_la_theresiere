<?php

namespace App\Entity;

use App\Repository\CarouselImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;

#[ORM\Entity(repositoryClass: CarouselImageRepository::class)]
class CarouselImage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $filename = null;

    #[ORM\Column(length: 255, nullable: true)] // Nouveau champ pour la légende
    private ?string $caption = null;

    #[ORM\Column]
    private ?int $ordre = null;

    // Utilisé uniquement pour l'upload de fichier (non mappé en base de données)
    private ?File $imageFile = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilename(): ?string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): static
    {
        $this->filename = $filename;

        return $this;
    }

    // NOUVEL ACCESSEUR pour la légende
    public function getCaption(): ?string
    {
        return $this->caption;
    }

    // NOUVEAU MUTATEUR pour la légende
    public function setCaption(?string $caption): static
    {
        $this->caption = $caption;

        return $this;
    }

    public function getOrdre(): ?int
    {
        return $this->ordre;
    }

    public function setOrdre(int $ordre): static
    {
        $this->ordre = $ordre;

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;
    }
}