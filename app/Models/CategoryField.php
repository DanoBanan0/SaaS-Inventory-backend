<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CategoryField extends Model
{
    use HasFactory;

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
