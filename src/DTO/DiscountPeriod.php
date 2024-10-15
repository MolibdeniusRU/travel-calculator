<?php

namespace App\DTO;

use App\Trait\PeriodTrait;
use Symfony\Component\Serializer\Attribute\SerializedPath;

class DiscountPeriod
{
    use PeriodTrait;

    #[SerializedPath('[discounts][*][start]')]
    public string|null $start;

    #[SerializedPath('[discounts][*][end]')]
    public string|null $end;

    #[SerializedPath('[discounts][*][percent]')]
    public int $percent;

    public function setPercent(int $percent): void
    {
        $this->percent = $percent;
    }

}