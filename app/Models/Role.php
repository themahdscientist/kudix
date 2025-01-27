<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use \Sushi\Sushi;

    protected $rows = [
        [
            'id' => self::ADMIN,
            'name' => 'admin',
            'description' => 'Administrator',
        ],
        [
            'id' => self::CASHIER,
            'name' => 'cashier',
            'description' => 'Cashier/Salesperson',
        ],
        [
            'id' => self::DOCTOR,
            'name' => 'doctor',
            'description' => 'Doctor/Medical Personnel',
        ],
        [
            'id' => self::CUSTOMER,
            'name' => 'customer',
            'description' => 'Regular/Patient',
        ],
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    const ADMIN = 1;

    const CASHIER = 2;

    const DOCTOR = 3;

    const CUSTOMER = 4;
}
