<?php

namespace App\Service\CostCalculator\Discounters;

use App\DTO\TripPeriod;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class EarlyBookingDiscounter implements DiscounterInterface
{
    private const PAST = '01.01.1970';
    private const FUTURE = '01.01.2100';

    private Carbon $tripDate;

    private Carbon $purchaseDate;

    /** @var TripPeriod[] */
    private array $periods;

    private int $cost;

    private int|null $maxDiscount;

    public function __construct(string $tripDate, string $purchaseDate, array $periods, int $cost = 0, int|null $maxDiscount = null)
    {
        $this->tripDate = Carbon::create($tripDate);
        $this->purchaseDate = Carbon::create($purchaseDate);
        $this->cost = $cost;
        $this->maxDiscount = $maxDiscount;
        $this->periods = $periods;
    }

    public function calculateDiscount(): int
    {
        $discountsPercents = [0];
        foreach ($this->periods as $period) {

            $tripPeriod = CarbonPeriod::create(
                $period->start ?? self::PAST,
                $period->end ?? self::FUTURE,
                CarbonPeriod::EXCLUDE_END_DATE
            );

            if ($tripPeriod->contains($this->tripDate)) {
                foreach ($period->discountPeriods as $discountPeriod) {

                    $purchasePeriod = CarbonPeriod::create(
                        $discountPeriod->start ?? self::PAST,
                        $discountPeriod->end ?? self::FUTURE,
                        CarbonPeriod::EXCLUDE_END_DATE
                    );

                    if ($purchasePeriod->contains($this->purchaseDate)) {
                        $discountsPercents[] = $discountPeriod->percent;
                    }
                }
            }
        }

        $discount = $this->cost / 100 * max($discountsPercents);

        if ($this->maxDiscount !== null) {
            $discount = min($discount, $this->maxDiscount);
        }

        return $discount;
    }

    public function setCost(int $cost): DiscounterInterface
    {
        $this->cost = $cost;

        return $this;
    }
}