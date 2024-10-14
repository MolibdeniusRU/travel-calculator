<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\DiscounterNameEnum;
use App\Service\CostCalculator\CostCalculator;
use App\Service\CostCalculator\Discounters\ChildrenDiscounter;
use App\Service\CostCalculator\Discounters\EarlyBookingDiscounter;
use Carbon\Carbon;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Yaml\Yaml;

class CalculatorController extends AbstractController
{
    #[Route(
        path: '/calculate',
        name: 'calculate',
        methods: 'POST',
    )]
    public function calculate(
        Request                                 $request,
        ValidatorInterface                      $validator,
        DenormalizerInterface $denormalizer,
        CostCalculator                          $calculator,
        #[Autowire('%discount_config%')] string $configPath
    ): Response
    {
        $query = $request->toArray();

        $constrains = new Assert\Collection([
            'cost' => new Assert\Required([
                new Assert\NotBlank,
                new Assert\Positive,
            ]),
            'birthday_date' => new Assert\Required([
                new Assert\NotBlank,
                new Assert\DateTime('d.m.Y'),
            ]),
            'trip_date' => new Assert\Optional([
                new Assert\DateTime('d.m.Y'),
            ]),
            'purchase_date' => new Assert\Optional([
                new Assert\DateTime('d.m.Y'),
            ])
        ]);

        $errors = $validator->validate($query, $constrains);

        if ($errors->count()) {
            return $this->json(['errors' => $errors], Response::HTTP_BAD_REQUEST);
        }

        try {

            if (!isset($query['trip_date'])) {
                $query['trip_date'] = Carbon::now()->format('d.m.Y');
            }

            $filename = $configPath . DiscounterNameEnum::CHILDREN_DISCOUNTER->value . '.yaml';

            if (!file_exists($filename)) {
                throw new \RuntimeException('Configuration for children`s discounter does not exist.');
            }

            $ageDiscounts = $denormalizer->denormalize(Yaml::parseFile($filename)['ageDiscounts'], 'App\DTO\AgeDiscount[]', 'any');

            $calculator->addDiscounter(
                name: DiscounterNameEnum::CHILDREN_DISCOUNTER->value,
                discounter: new ChildrenDiscounter(
                    tripDate: $query['trip_date'],
                    birthdayDate: $query['birthday_date'],
                    ageDiscounts: $ageDiscounts
                )
            );

            if ($query['purchase_date'] !== null) {
                $filename = $configPath . DiscounterNameEnum::EARLY_BOOKING_DISCOUNTER->value . '.yaml';

                if (!file_exists($filename)) {
                    throw new \RuntimeException('Configuration for early booking`s discounter does not exist.');
                }

                $periods = $denormalizer->denormalize(Yaml::parseFile($filename)['periods'], 'App\DTO\TripPeriod[]', 'any');
                $maxDiscount = (int)$_ENV['APP_EARLY_BOOKING_DISCOUNT_MAX'];

                $calculator->addDiscounter(
                    name: DiscounterNameEnum::EARLY_BOOKING_DISCOUNTER->value,
                    discounter: new EarlyBookingDiscounter(
                        tripDate: $query['trip_date'],
                        purchaseDate: $query['purchase_date'],
                        periods: $periods,
                        maxDiscount: $maxDiscount
                    ),
                    considerDiscount: true
                );
            }

            $calculator->setBaseCost($query['cost']);
            $cost = $calculator->calculate();

            return $this->json(['cost' => $cost]);

        } catch (\Throwable $exception) {
            return $this->json(['errors' => $exception->getMessage()], Response::HTTP_SERVICE_UNAVAILABLE);
        }
    }
}