<?php

namespace App\Tests\Service\CostCalculator;

use App\DTO\AgeDiscount;
use App\Enum\DiscounterNameEnum;
use App\Service\CostCalculator\CostCalculator;
use App\Service\CostCalculator\Discounters\ChildrenDiscounter;
use PHPUnit\Framework\TestCase;

class CostCalculatorTest extends TestCase
{
    public function testCalculate(): void
    {
        $calculator = new CostCalculator();

        $this->assertSame(0, $calculator->calculate());

        $ageDiscount = new AgeDiscount();
        $ageDiscount->years = 12;
        $ageDiscount->percent = 20;
        $ageDiscount->maxDiscount = 5000;

        $discounter = new ChildrenDiscounter('01.01.2024', '01.01.2013', [$ageDiscount]);

        $calculator->addDiscounter(DiscounterNameEnum::CHILDREN_DISCOUNTER->value, $discounter);
        $calculator->setBaseCost(10000);

        $this->assertSame(8000, $calculator->calculate());
    }
}
