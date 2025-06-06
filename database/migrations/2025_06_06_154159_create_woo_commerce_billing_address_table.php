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
        Schema::create('woo_commerce_billing_addresses', function (Blueprint $table) {
            $table->id();

            // Clave foránea que conecta con una orden específica.
            // unique() forza la relación de uno a uno (una orden solo puede tener una dirección de facturación).
            $table->foreignId('woo_commerce_order_id')->unique()->constrained('woo_commerce_orders')->onDelete('cascade');
            
            $table->string('first_name');
            $table->string('last_name');
            $table->string('company')->nullable();
            $table->text('address_1');
            $table->text('address_2')->nullable();
            $table->string('city');
            $table->string('state');
            $table->string('postcode');
            $table->string('country');
            $table->string('email');
            $table->string('phone');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woo_commerce_billing_addresses');
    }
};
