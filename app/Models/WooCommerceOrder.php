<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WooCommerceOrder extends Model
{
    use HasFactory;

    // Renombramos el campo 'id' de WooCommerce para evitar conflictos
    protected $fillable = [
        'woocommerce_id',
        'parent_id',
        'number',
        'order_key',
        'status',
        'currency',
        'prices_include_tax',
        'date_created',
        'date_modified',
        'discount_total',
        'discount_tax',
        'shipping_total',
        'shipping_tax',
        'cart_tax',
        'total',
        'total_tax',
        'customer_id',
        'payment_method',
        'payment_method_title',
        'transaction_id',
        'customer_ip_address',
        'customer_user_agent',
        'created_via',
        'customer_note',
        'date_completed',
        'date_paid',
        'cart_hash',
    ];

    // Casts para convertir tipos de datos automáticamente
    protected $casts = [
        'prices_include_tax' => 'boolean',
        'date_created' => 'datetime',
        'date_modified' => 'datetime',
        'date_completed' => 'datetime',
        'date_paid' => 'datetime',
        'total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        // ... otros casts para campos decimales
    ];

    // Relación: Una orden tiene muchos items
    public function lineItems(): HasMany
    {
        return $this->hasMany(WooCommerceLineItem::class);
    }
}