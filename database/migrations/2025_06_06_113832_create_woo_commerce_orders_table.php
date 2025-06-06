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
        Schema::create('woo_commerce_orders', function (Blueprint $table) {
            $table->id(); // ID local de tu base de datos
            $table->unsignedBigInteger('woocommerce_id')->unique(); // ID que viene de WooCommerce
            $table->unsignedBigInteger('parent_id')->default(0);
            $table->string('number');
            $table->string('order_key')->unique();
            $table->string('status');
            $table->string('currency', 10);
            $table->boolean('prices_include_tax');
            $table->dateTime('date_created')->nullable();
            $table->dateTime('date_modified')->nullable();
            $table->decimal('discount_total', 15, 2);
            $table->decimal('discount_tax', 15, 2);
            $table->decimal('shipping_total', 15, 2);
            $table->decimal('shipping_tax', 15, 2);
            $table->decimal('cart_tax', 15, 2);
            $table->decimal('total', 15, 2);
            $table->decimal('total_tax', 15, 2);
            $table->unsignedBigInteger('customer_id');
            $table->string('payment_method');
            $table->string('payment_method_title');
            $table->string('transaction_id')->nullable();
            $table->ipAddress('customer_ip_address')->nullable();
            $table->text('customer_user_agent')->nullable();
            $table->string('created_via');
            $table->text('customer_note')->nullable();
            $table->dateTime('date_completed')->nullable();
            $table->dateTime('date_paid')->nullable();
            $table->string('cart_hash')->nullable();
            $table->timestamps(); // Campos created_at y updated_at locales
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woo_commerce_orders');
    }
};
