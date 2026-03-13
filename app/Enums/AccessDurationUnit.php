<?php

namespace App\Enums;

enum AccessDurationUnit: string
{
    case Hour = 'hour';
    case Day = 'day';
    case Week = 'week';
    case Month = 'month';
    case Year = 'year';
}
