<?php

namespace App\DTO;

class AgeDiscount
{
    public int $years;
    public int $percent;
    public int|null $maxDiscount;
}