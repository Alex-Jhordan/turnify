<?php

namespace App\Enums;

enum DocumentType: string
{
    case DNI = 'dni';
    case Passport = 'passport';
    case CE = 'ce';
}
