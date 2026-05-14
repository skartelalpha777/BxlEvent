<?php

namespace App\Entity;

use App\Repository\ReportsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReportsRepository::class)]
class Reports
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $date = null;
    public function __construct()
    {
        // On initilise a la date courante
        $this->date = new \DateTime();
    }

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column]
    private ?bool $treated = false;

    #[ORM\ManyToOne(inversedBy: 'reports')]
    private ?Event $event = null;

    #[ORM\ManyToOne(inversedBy: 'reports')]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'reports')]
    private ?ReportCategory $category = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function isTreated(): ?bool
    {
        return $this->treated;
    }

    public function setTreated(bool $treated): static
    {
        $this->treated = $treated;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): static
    {
        $this->event = $event;

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

    public function getCategory(): ?ReportCategory
    {
        return $this->category;
    }

    public function setCategory(?ReportCategory $category): static
    {
        $this->category = $category;

        return $this;
    }
}
