<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Purchase extends Model implements Auditable
{
    use HasFactory, AuditableTrait, HasUuids;

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
