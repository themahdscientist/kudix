<?php

namespace App\Enums;

enum DocumentableType: string
{
    case Purchase = 'purchase';
    case Sale = 'sale';
}
