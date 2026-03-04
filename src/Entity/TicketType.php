<?php

namespace App\Entity;

use App\Enum\TicketLabel;
use App\Repository\TicketTypeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TicketTypeRepository::class)]
class TicketType
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $ticket = null;

    #[ORM\Column(enumType: TicketLabel::class)]
    private ?TicketLabel $label = null;

    #[ORM\Column]
    private ?int $price = null;

    #[ORM\Column]
    private ?int $eventId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicket(): ?int
    {
        return $this->ticket;
    }

    public function setTicket(int $ticket): static
    {
        $this->ticket = $ticket;

        return $this;
    }

    public function getLabel(): ?TicketLabel
    {
        return $this->label;
    }

    public function setLabel(TicketLabel $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getEventId(): ?int
    {
        return $this->eventId;
    }

    public function setEventId(int $eventId): static
    {
        $this->eventId = $eventId;

        return $this;
    }
}
