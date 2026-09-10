<?php

namespace App\Entity;

use App\Repository\TicketTypeTranslationRepository;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;

/**
 * Traduction de TicketType : uniquement label et description, une ligne
 * par langue. locale et la relation vers TicketType sont fournies par
 * TranslationTrait, injectées dans les métadonnées Doctrine par
 * TranslatableEventSubscriber (même mécanisme que EventTranslation).
 */
#[ORM\Entity(repositoryClass: TicketTypeTranslationRepository::class)]
class TicketTypeTranslation implements TranslationInterface
{
    use TranslationTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Accesseurs de secours pour l'admin EasyAdmin : getTranslatable() du
     * trait a un type de retour non-nullable, ce qui plante quand EasyAdmin
     * lit une entité toute neuve (formulaire "new") où $translatable est
     * encore vide. On lit/écrit directement la propriété du trait, qui elle
     * n'a pas cette contrainte.
     */
    public function getTicketType(): ?TicketType
    {
        return $this->translatable;
    }

    public function setTicketType(?TicketType $ticketType): static
    {
        $this->translatable = $ticketType;

        return $this;
    }
}
