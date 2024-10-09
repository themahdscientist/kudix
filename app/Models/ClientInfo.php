<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientInfo extends Model
{
    use HasFactory;

    protected $table = 'clients';

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'user_id');
    }

    public function loyaltyProgram(): BelongsTo
    {
        return $this->belongsTo(LoyaltyProgram::class);
    }
}
