<?php

namespace App\Enums;

enum ActivationMode: string
{
    case Purchase = 'purchase';
    case FirstCheckIn = 'first_check_in';
}
