<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use OwenIt\Auditing\Contracts\Auditable;
use OwenIt\Auditing\Auditable as AuditableTrait;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Category extends Model implements Auditable
{
    use HasFactory, AuditableTrait, HasUuids;

    protected $fillable = [
        'name',
    ];

    public function fields()
    {
        return $this->hasMany(CategoryField::class);
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}
