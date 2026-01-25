<?php

namespace App\Models;

use Illuminate\Container\Attributes\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\LogOptions;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_code',
        'serial_number',
        'brand',
        'model',
        'purchase_id',
        'category_id',
        'employee_id',
        'status',
        'specs',
        'comments',
    ];

    protected $casts = [
        'specs' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'employee_id', 'comments'])
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Dispositivo {$eventName}");
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}
