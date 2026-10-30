<?php

namespace App\Support;

class ThemePalette
{
    /** Default brand — sama dengan halaman login KucekPos */
    public const DEFAULT_PRIMARY = '#0B3D8C';

    public const DEFAULT_SECONDARY = '#64748B';

    /**
     * Preset template warna (urutan: KucekPos default dulu).
     *
     * @return list<array{name:string,primary_color:string,secondary_color:string}>
     */
    public static function presets(): array
    {
        return [
            ['name' => 'KucekPos', 'primary_color' => self::DEFAULT_PRIMARY, 'secondary_color' => self::DEFAULT_SECONDARY],
            ['name' => 'Ocean Blue', 'primary_color' => '#2563EB', 'secondary_color' => '#64748B'],
            ['name' => 'Teal Soft', 'primary_color' => '#278074', 'secondary_color' => '#627589'],
            ['name' => 'Forest', 'primary_color' => '#2F6B4F', 'secondary_color' => '#6B7280'],
            ['name' => 'Slate Navy', 'primary_color' => '#334155', 'secondary_color' => '#78716C'],
            ['name' => 'Rose Soft', 'primary_color' => '#BE5A6B', 'secondary_color' => '#6B7280'],
            ['name' => 'Amber Warm', 'primary_color' => '#B45309', 'secondary_color' => '#57534E'],
        ];
    }

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

    public static function normalize(string $hex, ?string $fallback = null): string
    {
        $fallback ??= self::DEFAULT_PRIMARY;
        $hex = trim($hex);
        if (! str_starts_with($hex, '#')) {
            $hex = '#'.$hex;
        }
        if (! preg_match('/^#[0-9A-Fa-f]{6}$/', $hex)) {
            return strtoupper($fallback);
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
