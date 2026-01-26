<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;

class Employee extends Model implements Auditable
{
    use HasFactory, AuditableTrait;

    protected $fillable = [
        'unit_id',
        'name',
        'email',
        'job_title',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}
