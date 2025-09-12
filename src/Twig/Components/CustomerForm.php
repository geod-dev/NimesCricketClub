<?php

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsLiveComponent]
final class CustomerForm
{
    use DefaultActionTrait;

    #[LiveProp]
    public \App\Entity\CustomerForm $form;

    #[LiveProp(writable: true)]
    public string $email = "";

    #[LiveProp(writable: true)]
    public string $last_name = "";

    #[LiveProp(writable: true)]
    public string $first_name = "";

    #[LiveProp(writable: true)]
    public string $phone = "";

    #[LiveProp(writable: true)]
    public array $data = [];

    #[PostMount]
    public function postMount(): void
    {
        $this->addEntry();
    }

    #[LiveAction]
    public function addEntry(): void
    {
        $this->data[] = $this->getEmptyData();
    }

    public function getEmptyData(): array
    {
        $data = [];
        foreach ($this->form->getFields() as $field) {
            $data[$field->getReference()] = '';
        }
        return $data;
    }

    #[LiveAction]
    public function removeEntry(#[LiveArg] int $index): void
    {
        unset($this->data[$index]);
        $this->data = array_values($this->data);
    }

    public function getPrice(): ?string
    {
        $totalPrice = $this->form->calculatePrice($this->data);
        if ($totalPrice === 0.0) return null;
        else return number_format($totalPrice, 2, ',', ' ') . ' €';
    }
}
