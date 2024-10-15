<?php

namespace App\Trait;

use Carbon\Carbon;

trait PeriodTrait
{
    public function setStart(string|null $start): void
    {
        $this->start = $start !== null ? $this->prepareDate($start) : null;
    }

    public function setEnd(string|null $end): void
    {
        $this->end = $end !== null ? $this->prepareDate($end) : null;
    }

    private function prepareDate(string $date): string
    {
        [$day, $month, $year] = explode('.', $date);

        if ($year === '{current-year}') {
            $year = (string)Carbon::now()->year;
        } elseif ($year === '{next-year}') {
            $year = (string)(Carbon::now()->addYear()->year);
        }

        return "{$day}.{$month}.{$year}";
    }
}