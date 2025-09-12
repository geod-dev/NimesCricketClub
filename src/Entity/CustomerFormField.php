<?php

namespace App\Entity;

use App\Entity\Abstract\AbstractUid;
use App\Repository\CustomerFormFieldRepository;
use App\Utils\FieldTypeType;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomerFormFieldRepository::class)]
class CustomerFormField extends AbstractUid
{
    #[ORM\Column(length: 255)]
    private ?string $nameFr = null;

    #[ORM\Column(length: 255)]
    private ?string $nameEn = null;

    #[ORM\Column(enumType: FieldTypeType::class)]
    private ?FieldTypeType $type = null;

    #[ORM\ManyToOne(inversedBy: 'fields')]
    #[ORM\JoinColumn(nullable: false)]
    private ?CustomerForm $form = null;

    #[ORM\Column]
    private ?bool $isRequired = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function getName(string $locale): ?string
    {
        if ($locale === "en") return $this->nameEn;
        else return $this->nameFr;
    }

    public function getNameFr(): ?string
    {
        return $this->nameFr;
    }

    public function setNameFr(string $nameFr): static
    {
        $this->nameFr = $nameFr;

        return $this;
    }

    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    public function setNameEn(string $nameEn): static
    {
        $this->nameEn = $nameEn;

        return $this;
    }

    public function getType(): ?FieldTypeType
    {
        return $this->type;
    }

    public function setType(FieldTypeType $type): CustomerFormField
    {
        $this->type = $type;

        return $this;
    }

    public function getForm(): ?CustomerForm
    {
        return $this->form;
    }

    public function setForm(?CustomerForm $form): static
    {
        $this->form = $form;

        return $this;
    }

    public function isRequired(): ?bool
    {
        return $this->isRequired;
    }

    public function setIsRequired(bool $isRequired): static
    {
        $this->isRequired = $isRequired;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }
}
