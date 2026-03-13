<?php

namespace App\Enums;

enum AccessCodeStrategy: string
{
    case Static = 'static';
    case RotateOnCheckIn = 'rotate_on_check_in';
}
