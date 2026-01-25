<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'purchase_date',
        'provider',
        'total_amount'
    ];

    protected $casts = [
        'purchase_date' => 'date',
    ];

    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}
