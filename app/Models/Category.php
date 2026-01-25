<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

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
