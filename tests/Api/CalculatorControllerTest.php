<?php

namespace App\Tests\Api;

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class CalculatorControllerTest extends WebTestCase
{
    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    public function testCalculate(): void
    {
        $client = static::createClient();
        $client->jsonRequest('POST', '/calculate', [
            'cost' => 10000,
            'birthday_date' => '01.01.2000',
            'trip_date' => '01.04.2025',
            'purchase_date' => '01.10.2024'
        ]);

        self::assertJson('{"cost": 9300}');

        $client->jsonRequest('POST', '/calculate', [
            'cost' => 10000,
            'trip_date' => '01.04.2025',
            'purchase_date' => '01.10.2024'
        ]);

        self::assertJson(
            '{
                      "errors": {
                        "type": "https://symfony.com/errors/validation",
                        "title": "Validation Failed",
                        "detail": "[birthday_date]: This field is missing.",
                        "violations": [
                          {
                            "propertyPath": "[birthday_date]",
                            "title": "This field is missing.",
                            "template": "This field is missing.",
                            "parameters": {
                              "{{ field }}": "\"birthday_date\""
                            },
                            "type": "urn:uuid:2fa2158c-2a7f-484b-98aa-975522539ff8"
                          }
                        ]
                      }
                    }'
        );
    }
}
