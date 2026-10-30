<?php

namespace App\Enums;

enum OrderStatus: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case DONE = 'done';
    case COMPLETED = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::TODO => 'Antrian',
            self::IN_PROGRESS => 'Proses',
            self::DONE => 'Selesai',
            self::COMPLETED => 'Diambil',
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
