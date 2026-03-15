<?php

namespace App\Entity;

use App\Enum\Status;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTime $hour = null;

    #[ORM\Column(enumType: Status::class)]
    private ?Status $status = null;

    #[ORM\ManyToOne(inversedBy: 'events')]
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
    #[ORM\OneToMany(targetEntity: Gallery::class, mappedBy: 'event')]
    private Collection $galleries;

    /**
     * @var Collection<int, TicketType>
     */
    #[ORM\OneToMany(targetEntity: TicketType::class, mappedBy: 'event')]
    private Collection $tickettypes;

    /**
     * @var Collection<int, Categorie>
     */
    #[ORM\ManyToMany(targetEntity: Categorie::class, inversedBy: 'events')]
    private Collection $categories;

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
}
