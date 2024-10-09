<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerExt extends Model
{
    use HasFactory;

    protected $table = 'customers';

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }

    public function loyaltyProgram(): BelongsTo
    {
        return $this->belongsTo(LoyaltyProgram::class);
    }
}
