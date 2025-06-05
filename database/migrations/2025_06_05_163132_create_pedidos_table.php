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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('AccountNum');
            $table->string('NombreClienteEntrega');
            $table->string('ClienteEntrega');
            $table->string('TelefonoEntrega');
            $table->string('DireccionEntrega');
            $table->string('StateId');
            $table->string('CountyId');
            $table->boolean('RecogerEnSitio')->default(false);
            $table->boolean('EntregaUsuarioFinal')->default(false);
            $table->string('dlvTerm')->nullable();
            $table->string('dlvmode')->nullable();
            $table->text('Observaciones');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
