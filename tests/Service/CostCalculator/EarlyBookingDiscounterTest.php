<?php

namespace App\Tests\Service\CostCalculator;

use App\DTO\DiscountPeriod;
use App\DTO\TripPeriod;
use App\Service\CostCalculator\Discounters\EarlyBookingDiscounter;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EarlyBookingDiscounterTest extends TestCase
{
    /** @var TripPeriod[] */
    private array $periods;

    protected function setUp(): void
    {
        $periods = [
            [
                "trip" => [
                    "start" => "01.04.2025",
                    "end" => "30.09.2025"
                ],
                "discounts" => [
                    [
                        "start" => null,
                        "end" => "30.11.2024",
                        "percent" => 7
                    ],
                    [
                        "start" => "01.12.2024",
                        "end" => "31.12.2024",
                        "percent" => 5
                    ],
                    [
                        "start" => "01.01.2025",
                        "end" => "31.01.2025",
                        "percent" => 3
                    ]
                ]
            ],
            [
                "trip" => [
                    "start" => "01.10.2024",
                    "end" => "14.01.2025"
                ],
                "discounts" => [
                    [
                        "start" => null,
                        "end" => "31.03.2024",
                        "percent" => 7
                    ],
                    [
                        "start" => "01.04.2024",
                        "end" => "31.04.2024",
                        "percent" => 5
                    ],
                    [
                        "start" => "01.05.2024",
                        "end" => "31.05.2024",
                        "percent" => 3
                    ]
                ]
            ],
            [
                "trip" => [
                    "start" => "15.01.2025",
                    "end" => null
                ],
                "discounts" => [
                    [
                        "start" => null,
                        "end" => "31.08.2024",
                        "percent" => 7
                    ],
                    [
                        "start" => "01.09.2024",
                        "end" => "31.09.2024",
                        "percent" => 5
                    ],
                    [
                        "start" => "01.10.2024",
                        "end" => "31.10.2024",
                        "percent" => 3
                    ]
                ]
            ]
        ];

        foreach ($periods as $item) {
            $period = new TripPeriod();
            $period->start = $item['trip']['start'];
            $period->end = $item['trip']['end'];
            foreach ($item['discounts'] as $discount) {
                $discountPeriod = new DiscountPeriod();
                $discountPeriod->start = $discount['start'];
                $discountPeriod->end = $discount['end'];
                $discountPeriod->percent = $discount['percent'];
                $period->discountPeriods[] = $discountPeriod;
            }
            $this->periods[] = $period;
        }

        parent::setUp();
    }

    public static function dataProvider(): array
    {
        return [
            [10000, '01.04.2025', '01.10.2024', 700],
            [10000, '01.04.2025', '01.12.2024', 500],
            [10000, '01.04.2025', '01.01.2025', 300],
            [10000, '01.10.2024', '01.02.2024', 700],
            [10000, '01.10.2024', '01.04.2024', 500],
            [10000, '01.10.2024', '01.05.2024', 300],
            [10000, '15.01.2025', '01.08.2024', 700],
            [10000, '15.01.2025', '01.09.2024', 500],
            [10000, '15.01.2025', '01.10.2024', 300],
            [30000, '15.01.2025', '01.08.2024', 1500],
            [10000, '15.04.2025', '01.09.2024', 700],
        ];
    }

    #[DataProvider('dataProvider')]
    public function testCalculateDiscounts(int $cost, string $tripDate, string $purchaseDate, int $expected): void
    {
        $discounter = new EarlyBookingDiscounter(
            tripDate: $tripDate,
            purchaseDate: $purchaseDate,
            periods: $this->periods,
            cost: $cost,
            maxDiscount: 1500
        );
        $this->assertSame($expected, $discounter->calculateDiscount());
    }
}
