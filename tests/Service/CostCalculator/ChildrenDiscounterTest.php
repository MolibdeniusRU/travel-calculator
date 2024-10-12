<?php

namespace App\Tests\Service\CostCalculator;

use App\DTO\AgeDiscount;
use App\Service\CostCalculator\Discounters\ChildrenDiscounter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ChildrenDiscounterTest extends TestCase
{
    /** @var AgeDiscount[] */
    private array $ageDiscounts;

    protected function setUp(): void
    {
        $ageDiscounts = [
            [
                'years' => 18,
                'percent' => 0,
                'maxDiscount' => null
            ],
            [
                'years' => 12,
                'percent' => 10,
                'maxDiscount' => null
            ],
            [
                'years' => 6,
                'percent' => 30,
                'maxDiscount' => 4500
            ],
            [
                'years' => 3,
                'percent' => 80,
                'maxDiscount' => null
            ]
        ];

        foreach ($ageDiscounts as $item) {
            $ageDiscount = new AgeDiscount();
            $ageDiscount->years = $item['years'];
            $ageDiscount->percent = $item['percent'];
            $ageDiscount->maxDiscount = $item['maxDiscount'];

            $this->ageDiscounts[] = $ageDiscount;
        }
        parent::setUp();
    }

    public static function dataProvider(): array
    {
        return [
            [10000, '01.01.2006', '01.01.2024', 0],
            [10000, '01.01.2007', '01.01.2024', 1000],
            [10000, '01.01.2013', '01.01.2024', 3000],
            [20000, '01.01.2013', '01.01.2024', 4500],
            [10000, '01.01.2019', '01.01.2024', 8000],
        ];
    }

    #[DataProvider('dataProvider')]
    public function testCalculateDiscount(int $cost, string $birthdayDate, string $tripDate, int $expected): void
    {
        $discounter = new ChildrenDiscounter(
            tripDate: $tripDate,
            birthdayDate: $birthdayDate,
            ageDiscounts: $this->ageDiscounts,
            cost: $cost
        );
        $this->assertSame($expected, $discounter->calculateDiscount());
    }
}
