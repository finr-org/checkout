<?php

namespace App\Entity;

class Product {
    protected string $type;
    protected string $name;
    protected int $price;

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
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

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public static function generateTestProduct(): Product
    {
        return (new Product())
            ->setType('T-shirt')
            ->setPrice(2800)
            ->SetName('T-shirt Checkout.com');
    }
}