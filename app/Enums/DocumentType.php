<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum DocumentType: string implements HasLabel
{
    case DNI = 'dni';
    case Passport = 'passport';
    case CE = 'ce';

    public function getLabel(): string | Htmlable | null
    {
        return match ($this) {
            self::DNI => 'DNI',
            self::Passport => 'Passport',
            self::CE => 'CE',
        };
    }
}
