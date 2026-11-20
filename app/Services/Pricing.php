<?php

declare(strict_types=1);

namespace App\Services;

final class Pricing
{
    /** Standard wholesale breaks, clipped so the first band starts at the product MOQ. */
    public static function defaultTiers(int $moq): array
    {
        $breaks = [
            ['min' => 12, 'max' => 49, 'discount' => 0],
            ['min' => 50, 'max' => 199, 'discount' => 8],
            ['min' => 200, 'max' => 499, 'discount' => 15],
            ['min' => 500, 'max' => null, 'discount' => 22],
        ];
        $tiers = [];
        foreach ($breaks as $b) {
            $min = (int)$b['min'];
            $max = $b['max'] !== null ? (int)$b['max'] : null;
            if ($max !== null && $max < $moq) {
                continue;
            }
            if ($min < $moq) {
                $min = $moq;
            }
            $tiers[] = ['min' => $min, 'max' => $max, 'discount' => (float)$b['discount']];
        }
        if (!$tiers) {
            $tiers[] = ['min' => $moq, 'max' => null, 'discount' => 0.0];
        }
        return $tiers;
    }

    /**
     * @param list<array{min:int|string,max:int|string|null,discount:float|int|string}> $tiers
     * @return array{unit:float,discount:float,min:int,max:?int}
     */
    public static function priceFor(float $base, array $tiers, int $qty): array
    {
        $applied = ['unit' => $base, 'discount' => 0.0, 'min' => 0, 'max' => null];
        foreach ($tiers as $t) {
            $min = (int)$t['min'];
            $max = $t['max'] === null || $t['max'] === '' ? null : (int)$t['max'];
            if ($qty >= $min && ($max === null || $qty <= $max)) {
                $off = (float)$t['discount'];
                $applied = [
                    'unit' => round($base * (1 - $off / 100), 2),
                    'discount' => $off,
                    'min' => $min,
                    'max' => $max,
                ];
            }
        }
        return $applied;
    }

    /**
     * @param list<array{min?:mixed,max?:mixed,discount?:mixed}> $tiers
     * @return list<string>
     */
    public static function validateTiers(int $moq, array $tiers): array
    {
        $errors = [];
        if (!$tiers) {
            return ['Add at least one price tier starting at the product MOQ.'];
        }
        $mins = [];
        foreach ($tiers as $i => $t) {
            $n = $i + 1;
            $min = isset($t['min']) && $t['min'] !== '' ? (int)$t['min'] : 0;
            $max = isset($t['max']) && $t['max'] !== '' && $t['max'] !== null ? (int)$t['max'] : null;
            $off = isset($t['discount']) && $t['discount'] !== '' ? (float)$t['discount'] : 0.0;
            if ($min <= 0) {
                $errors[] = "Tier {$n}: minimum quantity is required.";
                continue;
            }
            if ($min < $moq) {
                $errors[] = "Tier {$n}: minimum quantity ({$min}) cannot be below the product MOQ ({$moq}).";
            }
            if ($max !== null && $max < $min) {
                $errors[] = "Tier {$n}: maximum must be greater than or equal to the minimum.";
            }
            if ($off < 0 || $off > 90) {
                $errors[] = "Tier {$n}: discount must be between 0 and 90%.";
            }
            $mins[] = $min;
        }
        if ($mins && min($mins) !== $moq) {
            $errors[] = 'The first price tier must start at the product MOQ (' . $moq . ').';
        }
        return $errors;
    }
}
