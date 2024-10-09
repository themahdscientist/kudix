<?php

namespace App;

enum DocumentableType: string
{
    case Purchase = 'purchase';
    case Sale = 'sale';
}
