<?php

namespace App\DTO;

use Symfony\Component\Serializer\Attribute\SerializedPath;

class TripPeriod
{
    #[SerializedPath('[trip][start]')]
    public string|null $start;

    #[SerializedPath('[trip][end]')]
    public string|null $end;

    /** @var DiscountPeriod[] */
    #[SerializedPath('[discounts]')]
    public array $discountPeriods;
}