<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PARTIAL = 'partial';
    case PAID = 'paid';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Belum bayar',
            self::PARTIAL => 'DP / Sebagian',
            self::PAID => 'Lunas',
        };
    }

    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
