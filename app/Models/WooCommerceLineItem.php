<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WooCommerceLineItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'wc_order_id',
        'wc_id',
        'name',
        'product_id',
        'variation_id',
        'quantity',
        'tax_class',
        'subtotal',
        'subtotal_tax',
        'total',
        'total_tax',
        'sku',
        'price',
        'parent_name',
    ];
    
    // Casts para los tipos de dato
    protected $casts = [
        'price' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    // Relación inversa: Un item pertenece a una orden
    public function order(): BelongsTo
    {
        return $this->belongsTo(WooCommerceOrder::class, 'wc_order_id');
    }
}