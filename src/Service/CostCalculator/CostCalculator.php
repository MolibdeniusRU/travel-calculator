<?php

namespace App\Service\CostCalculator;

use App\Service\CostCalculator\Discounters\DiscounterInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class CostCalculator
{
    private Collection $discounters;

    private int $baseCost = 0;

    public function __construct()
    {
        $this->discounters = new ArrayCollection();
    }

    public function calculate(): int
    {
        $cost = $this->baseCost;

        if ($this->discounters->isEmpty()) {
            return $cost;
        }

        foreach ($this->discounters as ['name' => $name, 'discounter' => $discounter, 'considerDiscount' => $considerDiscount]) {
            if ($considerDiscount === false) {
                $cost = $this->baseCost;
            }

            /** @var  DiscounterInterface $discounter */
            $discount = $discounter
                ->setCost($cost)
                ->calculateDiscount();

            $cost -= $discount;
        }

        return $cost;
    }

    public function addDiscounter(string $name, DiscounterInterface $discounter, bool $considerDiscount = false): void
    {
        $this->discounters->add([
            'name' => $name,
            'discounter' => $discounter,
            'considerDiscount' => $considerDiscount
        ]);
    }

    public function removeDiscounter(string $name): Collection
    {
        return $this->discounters->remove($name);
    }

    public function setBaseCost(int $baseCost): static
    {
        $this->baseCost = $baseCost;

        return $this;
    }
}