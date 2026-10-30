<?php

namespace App\Support;

class PlanIcons
{
    public const OPTIONS = [
        'wash' => 'Cuci',
        'dry' => 'Kering',
        'iron' => 'Setrika',
        'express' => 'Express',
        'bed' => 'Bed Cover',
        'shoes' => 'Sepatu',
        'dryclean' => 'Dry Clean',
        'curtain' => 'Gorden',
        'blanket' => 'Selimut',
        'bag' => 'Tas',
        'sparkle' => 'Premium',
        'shirt' => 'Pakaian',
    ];

    public static function keys(): array
    {
        return array_keys(self::OPTIONS);
    }

    public static function options(): array
    {
        return collect(self::OPTIONS)
            ->map(fn (string $label, string $key) => ['key' => $key, 'label' => $label])
            ->values()
            ->all();
    }
}
