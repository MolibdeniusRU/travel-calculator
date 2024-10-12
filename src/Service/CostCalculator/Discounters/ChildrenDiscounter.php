<?php

namespace App\Service\CostCalculator\Discounters;

use App\DTO\AgeDiscount;
use Carbon\Carbon;

final class ChildrenDiscounter implements DiscounterInterface
{
    private int $cost;

    private string $birthdayDate;

    private array $ageDiscounts;

    private Carbon $tripDate;

    /**
     * @param AgeDiscount[] $ageDiscounts
     */
    public function __construct(string $tripDate, string $birthdayDate, array $ageDiscounts, int $cost = 0)
    {
        $this->cost = $cost;
        $this->birthdayDate = $birthdayDate;
        $this->tripDate = Carbon::create($tripDate);
        $this->ageDiscounts = $ageDiscounts;
    }

    public function calculateDiscount(): int
    {
        if (count($this->ageDiscounts) === 1) {
            return $this->calculate($this->ageDiscounts[0]->percent, $this->ageDiscounts[0]->maxDiscount);
        }

        usort($this->ageDiscounts, static function ($a, $b) {
            return $a->years < $b->years;
        });

        $olderAge = null;
        foreach ($this->ageDiscounts as $ageDiscount) {
            $youngerAge = Carbon::create($this->birthdayDate)?->addYears($ageDiscount->years);

            if (($olderAge === null) && $youngerAge < $this->tripDate) {
                return $this->calculate($ageDiscount->percent, $ageDiscount->maxDiscount);
            }

            if ($olderAge > $this->tripDate && $youngerAge < $this->tripDate) {
                return $this->calculate($ageDiscount->percent, $ageDiscount->maxDiscount);
            }

            $olderAge = $youngerAge;
        }

        return 0;
    }

    private function calculate(int $percent, int|null $maxDiscount): int
    {
        $discount = (int)$this->cost / 100 * $percent;

        if ($maxDiscount !== null) {
            $discount = min($discount, $maxDiscount);
        }

        return $discount;
    }

    public function setCost(int $cost): DiscounterInterface
    {
        $this->cost = $cost;

        return $this;
    }
}