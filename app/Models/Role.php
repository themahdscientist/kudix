<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;
    
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    const ADMIN = 1;

    const CASHIER = 2;

    const DOCTOR = 3;

    const CLIENT = 4;
}
