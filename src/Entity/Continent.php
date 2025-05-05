<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Validator\Constraints as CaptainConstraints;

/**
 * Continent.
 */
#[ORM\Table(name: 'continent')]
#[ORM\Entity]
class Continent implements \Stringable
{
    #[ORM\Column(name: 'id', type: Types::INTEGER)]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 255)]
    private ?string $name = null;

    #[ORM\Column(name: 'slug', type: Types::STRING, length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\OneToMany(mappedBy: 'continent', targetEntity: ContinentTranslation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[CaptainConstraints\Translation]
    private Collection $translations;

    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function setTranslations(Collection $translations): static
    {
        $this->translations = $translations;

        return $this;
    }

    public function addTranslation(ContinentTranslation $coaster): static
    {
        $coaster->setContinent($this);

        $this->translations->add($coaster);

        return $this;
    }

    public function removeTranslation(ContinentTranslation $coaster): static
    {
        $this->translations->removeElement($coaster);

        return $this;
    }
}
