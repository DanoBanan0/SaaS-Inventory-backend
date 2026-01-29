<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class CategoryField extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'category_id',
        'label',
        'key',
        'type',
        'required',
    ];

    protected $casts = [
        'required' => 'boolean'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
