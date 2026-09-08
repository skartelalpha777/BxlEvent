<?php

namespace App\Entity;

use App\Enum\Status;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['event:read']],
    denormalizationContext: ['groups' => ['event:write']],
    paginationItemsPerPage: 50, // Limite la taille de la réponse( le nombre d'object renvoyé) : sans pagination, toute la table est sérialisée à chaque appel et cela proque un ralentissement
    security: "is_granted('ROLE_USER')", // Nécessite une authentification pour accéder à cette ressource
    securityPostDenormalize: "is_granted('ROLE_USER')" // Contrôle après la désérialisation

)]

class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['event:read', 'event:write'])]
    #[Assert\NotBlank(message: 'Le titre est obligatoire.')]
    #[Assert\Length(max: 255, maxMessage: 'Le titre ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Groups(['event:read', 'event:write'])]
    #[Assert\NotBlank(message: 'La description est obligatoire.')]
    #[Assert\Length(max: 255, maxMessage: 'La description ne peut pas dépasser {{ limit }} caractères.')]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Groups(['event:read', 'event:write'])]
    #[Assert\NotNull(message: 'La date est obligatoire.')]
    private ?\DateTime $date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Groups(['event:read', 'event:write'])]
    #[Assert\NotNull(message: 'L\'heure est obligatoire.')]
    private ?\DateTime $hour = null;

    #[ORM\Column(enumType: Status::class)]
    private ?Status $status = Status::NOTCHECKED;

    #[ORM\ManyToOne(inversedBy: 'events')]
    #[Groups(['event:read', 'event:write'])]
    private ?Location $location = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
    private ?User $creator = null;

    /**
     * @var Collection<int, Ticket>
     */
    #[ORM\OneToMany(targetEntity: Ticket::class, mappedBy: 'event')]
    private Collection $tickets;

    /**
     * @var Collection<int, Reports>
     */
    #[ORM\OneToMany(targetEntity: Reports::class, mappedBy: 'event')]
    private Collection $reports;

    /**
     * @var Collection<int, Gallery>
     */
    #[ORM\OneToMany(targetEntity: Gallery::class, mappedBy: 'event', orphanRemoval: true)]
    private Collection $galleries;

    /**
     * @var Collection<int, TicketType>
     */
    #[ORM\OneToMany(targetEntity: TicketType::class, mappedBy: 'event', cascade: ['persist'], orphanRemoval: true)]
    private Collection $tickettypes;

    /**
     * @var Collection<int, Categorie>
     */
    #[ORM\ManyToMany(targetEntity: Categorie::class, inversedBy: 'events')]
    #[Groups(['event:read', 'event:write'])]
    private Collection $categories;

    #[ORM\Column]
    private ?bool $isFeatured = false;

    public function __construct()
    {
        $this->tickets = new ArrayCollection();
        $this->reports = new ArrayCollection();
        $this->galleries = new ArrayCollection();
        $this->tickettypes = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

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

    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getHour(): ?\DateTime
    {
        return $this->hour;
    }
    #[Groups(['event:read'])]
    public function getFormatedHour()
    {
        return $this->hour->format('H:i');
    }

    public function setHour(\DateTime $hour): static
    {
        $this->hour = $hour;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): static
    {
        $this->status = $status;

        return $this;
    }


    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getCreator(): ?User
    {
        return $this->creator;
    }

    public function setCreator(?User $creator): static
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * @return Collection<int, Ticket>
     */
    public function getTickets(): Collection
    {
        return $this->tickets;
    }

    public function addTicket(Ticket $ticket): static
    {
        if (!$this->tickets->contains($ticket)) {
            $this->tickets->add($ticket);
            $ticket->setEvent($this);
        }

        return $this;
    }

    public function removeTicket(Ticket $ticket): static
    {
        if ($this->tickets->removeElement($ticket)) {
            // set the owning side to null (unless already changed)
            if ($ticket->getEvent() === $this) {
                $ticket->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reports>
     */
    public function getReports(): Collection
    {
        return $this->reports;
    }

    public function addReport(Reports $report): static
    {
        if (!$this->reports->contains($report)) {
            $this->reports->add($report);
            $report->setEvent($this);
        }

        return $this;
    }

    public function removeReport(Reports $report): static
    {
        if ($this->reports->removeElement($report)) {
            // set the owning side to null (unless already changed)
            if ($report->getEvent() === $this) {
                $report->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Gallery>
     */
    public function getGalleries(): Collection
    {
        return $this->galleries;
    }

    public function addGallery(Gallery $gallery): static
    {
        if (!$this->galleries->contains($gallery)) {
            $this->galleries->add($gallery);
            $gallery->setEvent($this);
        }

        return $this;
    }

    public function removeGallery(Gallery $gallery): static
    {
        if ($this->galleries->removeElement($gallery)) {
            // set the owning side to null (unless already changed)
            if ($gallery->getEvent() === $this) {
                $gallery->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, TicketType>
     */
    public function getTickettypes(): Collection
    {
        return $this->tickettypes;
    }

    public function addTickettype(TicketType $tickettype): static
    {
        if (!$this->tickettypes->contains($tickettype)) {
            $this->tickettypes->add($tickettype);
            $tickettype->setEvent($this);
        }

        return $this;
    }

    public function removeTickettype(TicketType $tickettype): static
    {
        if ($this->tickettypes->removeElement($tickettype)) {
            // set the owning side to null (unless already changed)
            if ($tickettype->getEvent() === $this) {
                $tickettype->setEvent(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Categorie>
     */
    public function getCategories(): Collection
    {
        return $this->categories;
    }
    #[Groups(['event:read'])]
    public function getCategoryNames(): array
    {
        $names = [];
        foreach ($this->categories as $categorie) {
            $names[] = $categorie->getName();
        }
        return $names;
    }

    #[Groups(['event:read'])]
    public function getEventTicketTypes(): array
    {
        $types = [];
        foreach ($this->tickettypes as $ticketType) {
            array_push($types, [
                'label' => $ticketType->getLabel(),
                'prix' => $ticketType->getPrice(),
                'maxBillet' => $ticketType->getMaxTicket(),
            ]);
        }
        return $types;
    }

    #[Groups(['event:read'])]
    public function getLocationName(): ?string
    {
        return $this->location?->getName();
    }



    public function addCategory(Categorie $category): static
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(Categorie $category): static
    {
        $this->categories->removeElement($category);

        return $this;
    }

    function __toString()
    {
        return $this->title;
    }

    public function isFeatured(): ?bool
    {
        return $this->isFeatured;
    }

    public function setIsFeatured(bool $isFeatured): static
    {
        $this->isFeatured = $isFeatured;

        return $this;
    }
}
