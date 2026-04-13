<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'name',
        'stock',
        'repair_count'
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function lendings()
    {
        return $this->hasMany(Lending::class);
    }

    public function lendingTotal(): Attribute
    {
        return Attribute::make(
            get: fn() => (int) $this->lendings()
                ->where('status', 'borrowed')
                ->count()
        );
    }

    public function borrowedQty(): Attribute
    {
        return Attribute::make(
            get: fn() => (int) $this->lendings()
                ->where('status', 'borrowed')
                ->sum('qty')
        );
    }

    public function available(): Attribute
    {
        return Attribute::make(
            get: fn() => max(0, (int)$this->stock - (int)$this->repair_count - (int)$this->borrowed_qty)
        );
    }
}
