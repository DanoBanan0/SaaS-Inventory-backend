<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
// use Illuminate\Database\Eloquent\Casts\Attribute; // <--- Ya no necesitamos esto

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'unit_id',
        'name',
        'email',
        'job_title',
        'status',
    ];

    // ELIMINAMOS la función protected function status(): Attribute

    // AGREGAMOS esto para que Laravel maneje el booleano automáticamente
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
