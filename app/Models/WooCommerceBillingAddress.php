<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WooCommerceBillingAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'wc_order_id',
        'first_name',
        'last_name',
        'dni',
        'company',
        'address_1',
        'address_2',
        'city',
        'state',
        'postcode',
        'country',
        'email',
        'phone',
    ];

    /**
     * Obtiene la orden a la que pertenece esta dirección de facturación.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(WooCommerceOrder::class, 'wc_order_id');
    }
}
