<?php

namespace App\Entity;

use App\Repository\ConceptRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConceptRepository::class)]
class Concept
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $image = null;

    #[ORM\OneToMany(mappedBy: 'concept', targetEntity: Component::class, orphanRemoval: true)]
    private Collection $composants;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $defaultLanguage = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\ManyToOne(inversedBy: 'concepts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    public function __construct()
    {
        $this->composants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, Component>
     */
    public function getComposants(): Collection
    {
        return $this->composants;
    }

    public function addComposant(Component $composant): static
    {
        if (!$this->composants->contains($composant)) {
            $this->composants->add($composant);
            $composant->setConcept($this);
        }

        return $this;
    }

    public function removeComposant(Component $composant): static
    {
        if ($this->composants->removeElement($composant)) {
            // set the owning side to null (unless already changed)
            if ($composant->getConcept() === $this) {
                $composant->setConcept(null);
            }
        }

        return $this;
    }

    public function getDefaultLanguage(): ?Language
    {
        return $this->defaultLanguage;
    }

    public function setDefaultLanguage(?Language $defaultLanguage): static
    {
        $this->defaultLanguage = $defaultLanguage;

        return $this;
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

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }
}
