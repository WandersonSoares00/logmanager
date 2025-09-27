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
        Schema::create('meli_accounts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('meli_user_id')->unique()->comment('ID do usuário no Mercado Livre');
            $table->string('nickname');
            $table->text('access_token');
            $table->text('refresh_token');
            $table->timestamp('expires_at')->comment('Timestamp de quando o token expira');
            $table->timestamps(); // created_at e updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meli_accounts');
    }
};
