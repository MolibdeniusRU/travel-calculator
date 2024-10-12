<?php

namespace App\Service\CostCalculator\Discounters;

interface DiscounterInterface
{
    public function calculateDiscount(): int;

    public function setCost(int $cost): DiscounterInterface;
}