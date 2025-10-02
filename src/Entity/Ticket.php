<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'L\'email de l\'auteur ne peut pas être vide')]
    #[Assert\Email(message: 'L\'email {{ value }} n\'est pas un email valide.')]
    private ?string $authorEmail = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $openedAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $closedAt = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: 'La description ne peut pas être vide')]
    #[Assert\Length(
        min: 20,
        max: 250,
        minMessage: 'La description doit contenir au moins {{ limit }} caractères',
        maxMessage: 'La description ne peut pas contenir plus de {{ limit }} caractères'
    )]
    private ?string $description = null;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'La catégorie est obligatoire')]
    private ?Category $category = null;

    #[ORM\ManyToOne(inversedBy: 'tickets')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Status $status = null;

    #[ORM\ManyToOne(inversedBy: 'assignedTickets')]
    private ?User $responsible = null;

    public function __construct()
    {
        $this->openedAt = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthorEmail(): ?string
    {
        return $this->authorEmail;
    }

    public function setAuthorEmail(string $authorEmail): static
    {
        $this->authorEmail = $authorEmail;

        return $this;
    }

    public function getOpenedAt(): ?\DateTimeInterface
    {
        return $this->openedAt;
    }

    public function setOpenedAt(\DateTimeInterface $openedAt): static
    {
        $this->openedAt = $openedAt;

        return $this;
    }

    public function getClosedAt(): ?\DateTimeInterface
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTimeInterface $closedAt): static
    {
        $this->closedAt = $closedAt;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(?Status $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getResponsible(): ?User
    {
        return $this->responsible;
    }

    public function setResponsible(?User $responsible): static
    {
        $this->responsible = $responsible;

        return $this;
    }

    /**
     * Ferme le ticket en définissant la date de clôture
     */
    public function close(): static
    {
        $this->closedAt = new \DateTime();

        return $this;
    }

    /**
     * Vérifie si le ticket est fermé
     */
    public function isClosed(): bool
    {
        return $this->closedAt !== null;
    }

    /**
     * Calcule la durée d'ouverture du ticket
     */
    public function getOpenDuration(): ?\DateInterval
    {
        if ($this->openedAt === null) {
            return null;
        }

        $endDate = $this->closedAt ?? new \DateTime();
        
        return $this->openedAt->diff($endDate);
    }

    public function __toString(): string
    {
        return sprintf('#%d - %s', $this->id ?? 0, substr($this->description ?? '', 0, 50));
    }
}