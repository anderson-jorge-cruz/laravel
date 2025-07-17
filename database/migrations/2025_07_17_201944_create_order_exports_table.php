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
        Schema::create('order_exports', function (Blueprint $table) {
            $table->id();
            $table->string('idromaneio');
            $table->string('codromaneio');
            $table->string('pedido')->nullable();
            $table->string('notafiscal');
            $table->string('dataesperadaembarque');
            $table->string('horaesperadaembarque')->nullable();
            $table->string('depositante');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_exports');
    }
};
