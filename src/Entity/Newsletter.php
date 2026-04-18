<?php

namespace App\Entity;

use App\Repository\NewsletterRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NewsletterRepository::class)]
class Newsletter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $inscriptionDate = null;

    public function __construct()
{
    // On initilise a la date courante
    $this->inscriptionDate = new \DateTime(); 
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

    public function getInscriptionDate(): ?\DateTime
    {
        return $this->inscriptionDate;
    }

    public function setInscriptionDate(\DateTime $inscriptionDate): static
    {
        $this->inscriptionDate = $inscriptionDate;

        return $this;
    }
}
