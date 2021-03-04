<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'subcategory_id',
        'article',
        'name',
        'price',
        'price_old',
        'status'
    ];

    protected $casts = [
        'price' => 'integer',
        'price_old' => 'integer',
        'status' => 'boolean'
    ];

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }
}
