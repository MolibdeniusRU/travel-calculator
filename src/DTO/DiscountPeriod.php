<?php

namespace App\DTO;

use Symfony\Component\Serializer\Attribute\SerializedPath;

class DiscountPeriod
{
    #[SerializedPath('[discounts][*][start]')]
    public string|null $start;

    #[SerializedPath('[discounts][*][end]')]
    public string|null $end;

    #[SerializedPath('[discounts][*][percent]')]
    public int $percent;

}