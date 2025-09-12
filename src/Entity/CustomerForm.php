<?php

namespace App\Entity;

use App\Entity\Abstract\AbstractUid;
use App\Entity\Interface\TranslatableInterface;
use App\Entity\Trait\TimestampTrait;
use App\Entity\Trait\TranslatableTrait;
use App\Repository\CustomerFormRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomerFormRepository::class)]
#[ORM\HasLifecycleCallbacks]
class CustomerForm extends AbstractUid implements TranslatableInterface
{
    use TranslatableTrait;
    use TimestampTrait;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    /**
     * @var Collection<int, CustomerFormEntry>
     */
    #[ORM\OneToMany(mappedBy: 'form', targetEntity: CustomerFormEntry::class, orphanRemoval: true)]
    private Collection $entries;

    /**
     * @var Collection<int, CustomerFormField>
     */
    #[ORM\OneToMany(mappedBy: 'form', targetEntity: CustomerFormField::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $fields;

    #[ORM\Column(length: 255)]
    private ?string $price = null;

    #[ORM\Column]
    private ?bool $isRepeatable = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $entityNameFr = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $entityNameEn = null;

    public function __construct()
    {
        parent::__construct();
        $this->entries = new ArrayCollection();
        $this->fields = new ArrayCollection();
    }

    public function calculatePrice(array $data): float
    {
        $totalPrice = 0;
        foreach ($data as $data_entity) {
            $price = $this->getPrice();
            foreach ($data_entity as $key => $value) {
                $price = str_replace('{' . $key . '}', floatval($value), $price);
            }
            $totalPrice += eval('return ' . $price . ';');
        }
        return $totalPrice;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getTitleFr();
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): CustomerForm
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection<int, CustomerFormEntry>
     */
    public function getEntries(): Collection
    {
        return $this->entries;
    }

    public function addEntry(CustomerFormEntry $entry): static
    {
        if (!$this->entries->contains($entry)) {
            $this->entries->add($entry);
            $entry->setForm($this);
        }

        return $this;
    }

    public function removeEntry(CustomerFormEntry $entry): static
    {
        if ($this->entries->removeElement($entry)) {
            // set the owning side to null (unless already changed)
            if ($entry->getForm() === $this) {
                $entry->setForm(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, CustomerFormField>
     */
    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function addField(CustomerFormField $field): static
    {
        if (!$this->fields->contains($field)) {
            $this->fields->add($field);
            $field->setForm($this);
        }

        return $this;
    }

    public function removeField(CustomerFormField $field): static
    {
        if ($this->fields->removeElement($field)) {
            // set the owning side to null (unless already changed)
            if ($field->getForm() === $this) {
                $field->setForm(null);
            }
        }

        return $this;
    }

    public function isRepeatable(): ?bool
    {
        return $this->isRepeatable;
    }

    public function setIsRepeatable(bool $isRepeatable): static
    {
        $this->isRepeatable = $isRepeatable;

        return $this;
    }

    public function getEntityName(string $locale): ?string
    {
        if ($locale === 'en') return $this->entityNameEn;
        else return $this->entityNameFr;
    }

    public function getEntityNameFr(): ?string
    {
        return $this->entityNameFr;
    }

    public function setEntityNameFr(?string $entityNameFr): static
    {
        $this->entityNameFr = $entityNameFr;

        return $this;
    }

    public function getEntityNameEn(): ?string
    {
        return $this->entityNameEn;
    }

    public function setEntityNameEn(?string $entityNameEn): static
    {
        $this->entityNameEn = $entityNameEn;

        return $this;
    }
}
