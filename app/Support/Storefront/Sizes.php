<?php

namespace App\Support\Storefront;

/**
 * Figure-size system used by clothing (e.g. waistcoats). Numeric sizes group
 * into fit bands by age/build for the two-step fit → size selector and the
 * shop's size filter. Letter sizes ("S", "One Size") are "other".
 */
class Sizes
{
    public const BANDS = [
        ['key' => 'kids', 'label' => 'Kids (Gen Alpha)', 'shortLabel' => 'Kids', 'sizes' => [14, 16, 18, 20, 22, 24, 26]],
        ['key' => 'teens', 'label' => 'Boys / Teens', 'shortLabel' => 'Teens', 'sizes' => [28, 30, 32]],
        ['key' => 'genz', 'label' => 'Gen Z', 'shortLabel' => 'Gen Z', 'sizes' => [34, 36, 38, 40, 42]],
        ['key' => 'plus', 'label' => 'Plus Size', 'shortLabel' => 'Plus', 'sizes' => [44, 46, 48, 50]],
    ];

    /** @return list<int> Every figure size across all bands, ascending. */
    public static function allFigureSizes(): array
    {
        return array_merge(...array_column(self::BANDS, 'sizes'));
    }

    /** The band a numeric size belongs to, or null for "S", "One Size", etc. */
    public static function bandFor(string|int $size): ?array
    {
        $trimmed = trim((string) $size);
        if (! is_numeric($trimmed)) {
            return null;
        }
        $n = (float) $trimmed;
        foreach (self::BANDS as $band) {
            if (in_array($n, $band['sizes'], false)) {
                return $band;
            }
        }

        return null;
    }

    /**
     * Group raw size strings into bands.
     *
     * @param  list<string>  $sizes
     * @return array{banded: list<array{band: array, sizes: list<string>}>, other: list<string>}
     */
    public static function group(array $sizes): array
    {
        $buckets = [];
        $other = [];
        foreach ($sizes as $raw) {
            $band = self::bandFor($raw);
            if ($band === null) {
                $other[] = (string) $raw;

                continue;
            }
            $buckets[$band['key']][] = (string) $raw;
        }

        $banded = [];
        foreach (self::BANDS as $band) {
            if (! isset($buckets[$band['key']])) {
                continue;
            }
            $list = $buckets[$band['key']];
            usort($list, fn ($a, $b) => (float) $a <=> (float) $b);
            $banded[] = ['band' => $band, 'sizes' => array_values(array_unique($list))];
        }

        return ['banded' => $banded, 'other' => $other];
    }

    /**
     * True when the two-step fit → size picker applies: every size is numeric
     * and they span at least two bands.
     *
     * @param  list<string>  $sizes
     */
    public static function usesBands(array $sizes): bool
    {
        $grouped = self::group($sizes);

        return $grouped['other'] === [] && count($grouped['banded']) >= 2;
    }
}
