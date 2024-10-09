<?php

namespace App;

enum ClientType: string
{
    case Customer = 'customer';
    case Patient = 'patient';
}
