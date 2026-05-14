<?php

namespace App\Entity;

use App\Repository\ReportCategoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReportCategoryRepository::class)]
class ReportCategory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icon = null;

    /**
     * @var Collection<int, Reports>
     */
    #[ORM\OneToMany(targetEntity: Reports::class, mappedBy: 'category')]
    private Collection $reports;

    public function __construct()
    {
        $this->reports = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;

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
            $report->setCategory($this);
        }

        return $this;
    }

    public function removeReport(Reports $report): static
    {
        if ($this->reports->removeElement($report)) {
            // set the owning side to null (unless already changed)
            if ($report->getCategory() === $this) {
                $report->setCategory(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->label;
    }
}
