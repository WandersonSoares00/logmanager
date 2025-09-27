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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('meli_order_id')->unique()->comment('ID do pedido no Mercado Livre');

            // Associar o pedido a uma conta
            $table->foreignId('meli_account_id')->constrained('meli_accounts')->onDelete('cascade');

            $table->string('status');
            $table->decimal('total_amount', 10, 2);
            $table->bigInteger('shipping_id')->nullable();

            $table->string('shipping_label_url')->nullable();
            $table->string('shipping_label_local_path')->nullable();

            $table->json('customer_data')->nullable();

            // Timestamps para SLA
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('ready_to_ship_at')->nullable();
            $table->timestamp('shipped_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
