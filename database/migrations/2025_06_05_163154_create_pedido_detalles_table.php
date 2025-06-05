<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pedido_detalles', function (Blueprint $table) {
            $table->id();
            // Clave foránea que enlaza con la tabla 'pedidos'
            $table->foreignId('pedido_id')->constrained()->onDelete('cascade');
            $table->string('PartNum');
            $table->integer('Cantidad');
            $table->string('Marks');
            $table->string('Bodega');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedido_detalles');
    }
};
