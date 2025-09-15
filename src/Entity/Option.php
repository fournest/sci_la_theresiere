<?php

namespace App\Entity;

use App\Repository\OptionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: OptionRepository::class)]
#[ORM\Table(name: '`option`')]
class Option
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\ManyToMany(targetEntity: Reservation::class, mappedBy: 'options')]
    // Définit une relation "plusieurs à plusieurs" avec l'entité Reservation. 'mappedBy' indique que la propriété "options" dans Reservation est le côté propriétaire de la relation.
    private Collection $reservations;

    public function __construct()
    {
        $this->reservations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection<int, Reservation>
     */
    public function getReservations(): Collection
    {
        return $this->reservations;
    }

    public function addReservation(Reservation $reservation): static
    {
          // Vérification si la réservation n'est pas déjà dans la collection.
        if (!$this->reservations->contains($reservation)) {
             // Ajout.
            $this->reservations->add($reservation);
             // Ajout de cette option à la réservation correspondante (côté "Reservation").
            $reservation->addOption($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        // Retrait de l'élément de la collection.
        if ($this->reservations->removeElement($reservation)) {
            //  Retrait de cette option dans la réservation correspondante (côté "Reservation").
            $reservation->removeOption($this);
        }

        return $this;
    }
}
