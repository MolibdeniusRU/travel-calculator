<?php

namespace App\DTO;

use App\Trait\PeriodTrait;
use Symfony\Component\Serializer\Attribute\SerializedPath;

class TripPeriod
{
    use PeriodTrait;
    #[SerializedPath('[trip][start]')]
    public string|null $start;

    #[SerializedPath('[trip][end]')]
    public string|null $end;

    /** @var DiscountPeriod[] */
    #[SerializedPath('[discounts]')]
    public array $discountPeriods;

    public function setDiscountPeriods(array $discountPeriods): void
    {
        $this->discountPeriods = $discountPeriods;
    }

}