<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\NotBlank(message: "Veuillez renseigner votre email.")]
    #[Assert\Email(message: "Le format de l'email est invalide.")]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(length: 255)]
    private ?string $prenom = null;

    #[ORM\Column(length: 255)]
    private ?string $pseudo = null;

    /**
     * @var Collection<int, Reservation>
     */
    #[ORM\OneToMany(targetEntity: Reservation::class, mappedBy: 'user')]
    private Collection $reservations;

    /**
     * @var Collection<int, Visite>
     */
    #[ORM\OneToMany(targetEntity: Visite::class, mappedBy: 'user')]
    private Collection $visites;

    #[ORM\Column]
    private ?bool $isActive = null;

    #[ORM\Column]
    private ?bool $isBanned = null;

    /**
     * @var Collection<int, Contact>
     */
    // Collection des messages envoyés par cet utilisateur.
    #[ORM\OneToMany(targetEntity: Contact::class, mappedBy: 'sender', cascade: ['remove'])]
    private Collection $sentMessages;

    /**
     * @var Collection<int, Contact>
     */
    // Collection des messages reçus par cet utilisateur.
    #[ORM\OneToMany(targetEntity: Contact::class, mappedBy: 'recipient', cascade: ['remove'])]
    private Collection $receivedMessages;

    // Constructeur de la classe User.
    public function __construct()
    {
        $this->reservations = new ArrayCollection();
        $this->visites = new ArrayCollection();
        // Initialisation du compte comme actif par défaut.
        $this->isActive = true;
        // Initialisation du compte comme non banni par défaut.
        $this->isBanned = false;
        $this->sentMessages = new ArrayCollection();
        $this->receivedMessages = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles(), true);
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

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
            // Association de l'utilisateur à la réservation.
            $reservation->setUser($this);
        }

        return $this;
    }

    public function removeReservation(Reservation $reservation): static
    {
        if ($this->reservations->removeElement($reservation)) {
            // set the owning side to null (unless already changed)
            if ($reservation->getUser() === $this) {
                $reservation->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Visite>
     */
    public function getVisites(): Collection
    {
        return $this->visites;
    }

    public function addVisite(Visite $visite): static
    {
        // Vérification si la visite n'est pas déjà dans la collection.
        if (!$this->visites->contains($visite)) {
            // Ajout.
            $this->visites->add($visite);
            // Association de l'utilisateur à la visite.
            $visite->setUser($this);
        }

        return $this;
    }

    public function removeVisite(Visite $visite): static
    {
        if ($this->visites->removeElement($visite)) {
            if ($visite->getUser() === $this) {
                $visite->setUser(null);
            }
        }

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function isBanned(): ?bool
    {
        return $this->isBanned;
    }

    public function setIsBanned(bool $isBanned): static
    {
        $this->isBanned = $isBanned;

        return $this;
    }




    public function getSentMessages(): Collection
    {
        return $this->sentMessages;
    }

    public function getReceivedMessages(): Collection
    {
        return $this->receivedMessages;
    }
    public function addSentMessage(Contact $message): self
    {
        // Vérification si la visite n'est pas déjà dans la collection.
        if (!$this->sentMessages->contains($message)) {
            // Ajout.
            $this->sentMessages->add($message);
            //  Définition de l'expediteur du message.
            $message->setSender($this);
        }

        return $this;
    }

    public function removeSentMessage(Contact $message): self
    {
        if ($this->sentMessages->removeElement($message)) {
            if ($message->getSender() === $this) {
                $message->setSender(null);
            }
        }

        return $this;
    }



    public function addReceivedMessage(Contact $message): self
    {
        // Vérification si la visite n'est pas déjà dans la collection.
        if (!$this->receivedMessages->contains($message)) {
            // Ajout.
            $this->receivedMessages->add($message);
            //  Définition du destinataire du message.
            $message->setRecipient($this);
        }

        return $this;
    }

    public function removeReceivedMessage(Contact $message): self
    {
        if ($this->receivedMessages->removeElement($message)) {
            if ($message->getRecipient() === $this) {
                $message->setRecipient(null);
            }
        }

        return $this;
    }
}
