<?php

namespace App\Support;

class ThemePalette
{
    /**
     * Bangun skala warna (50–900) dari hex utama.
     *
     * @return array<string, string> hex per shade
     */
    public static function fromHex(string $hex): array
    {
        $hex = self::normalize($hex);
        [$r, $g, $b] = self::hexToRgb($hex);

        $mix = function (float $t, array $target) use ($r, $g, $b): string {
            $nr = (int) round($r + ($target[0] - $r) * $t);
            $ng = (int) round($g + ($target[1] - $g) * $t);
            $nb = (int) round($b + ($target[2] - $b) * $t);

            return self::rgbToHex($nr, $ng, $nb);
        };

        return [
            '50' => $mix(0.92, [255, 255, 255]),
            '100' => $mix(0.80, [255, 255, 255]),
            '200' => $mix(0.62, [255, 255, 255]),
            '300' => $mix(0.42, [255, 255, 255]),
            '400' => $mix(0.20, [255, 255, 255]),
            '500' => $mix(0.05, [255, 255, 255]),
            '600' => $hex,
            '700' => $mix(0.18, [0, 0, 0]),
            '800' => $mix(0.32, [0, 0, 0]),
            '900' => $mix(0.45, [0, 0, 0]),
        ];
    }

    /**
     * @return array<string, string> rgb triplet "r g b" untuk CSS variables
     */
    public static function cssRgbVars(string $hex): array
    {
        $out = [];
        foreach (self::fromHex($hex) as $shade => $value) {
            [$r, $g, $b] = self::hexToRgb($value);
            $out[$shade] = "{$r} {$g} {$b}";
        }

        return $out;
    }

    public static function normalize(string $hex): string
    {
        $hex = trim($hex);
        if (! str_starts_with($hex, '#')) {
            $hex = '#'.$hex;
        }
        if (! preg_match('/^#[0-9A-Fa-f]{6}$/', $hex)) {
            return '#278074';
        }

        return strtoupper($hex);
    }

    /** @return array{0:int,1:int,2:int} */
    private static function hexToRgb(string $hex): array
    {
        $hex = ltrim(self::normalize($hex), '#');

        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }

    private static function rgbToHex(int $r, int $g, int $b): string
    {
        return sprintf('#%02X%02X%02X', max(0, min(255, $r)), max(0, min(255, $g)), max(0, min(255, $b)));
    }
}
