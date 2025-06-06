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
        Schema::create('woo_commerce_line_items', function (Blueprint $table) {
            $table->id(); // ID local
            // Clave foránea para relacionar con la orden principal
            $table->foreignId('woo_commerce_orders_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('wc_id')->unique(); // El ID del line_item que viene de WooCommerce
            $table->text('name');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variation_id');
            $table->integer('quantity');
            $table->string('tax_class')->nullable();
            $table->decimal('subtotal', 15, 2);
            $table->decimal('subtotal_tax', 15, 2);
            $table->decimal('total', 15, 2);
            $table->decimal('total_tax', 15, 2);
            $table->string('sku')->nullable();
            $table->decimal('price', 15, 2);
            $table->string('parent_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woo_commerce_line_items');
    }
};
