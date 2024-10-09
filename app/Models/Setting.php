<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $hidden = [
        'bank_acc_no',
    ];

    protected function casts(): array
    {
        return [
            'bank_acc_no' => 'hashed',
            'is_subscribed' => 'boolean',
        ];
    }
}
