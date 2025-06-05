<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    use HasFactory;

    protected $fillable = [
        'AccountNum',
        'NombreClienteEntrega',
        'ClienteEntrega',
        'TelefonoEntrega',
        'DireccionEntrega',
        'StateId',
        'CountyId',
        'RecogerEnSitio',
        'EntregaUsuarioFinal',
        'dlvTerm',
        'dlvmode',
        'Observaciones',
    ];

    /**
     * Define la relación de uno a muchos con PedidoDetalle.
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(PedidoDetalle::class);
    }
}
