<?php

namespace App\Entity;

use App\Repository\EventTranslationRepository;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;


#[ORM\Entity(repositoryClass: EventTranslationRepository::class)]
class EventTranslation implements TranslationInterface
{
    use TranslationTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    public function getId(): ?int
    {
        return $this->id;
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


    public function getEvent(): ?Event
    {
        return $this->translatable;
    }

    public function setEvent(?Event $event): static
    {
        $this->translatable = $event;

        return $this;
    }
}
