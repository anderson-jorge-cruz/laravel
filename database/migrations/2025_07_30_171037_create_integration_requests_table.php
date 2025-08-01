<?php

use App\Models\IntegrationConfig;
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
        Schema::create('integration_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(IntegrationConfig::class);
            $table->string('invoice_number');
            $table->string('order_number');
            $table->string('collect_number');
            $table->json('request_data');
            $table->json('response_data')->nullable();
            $table->enum('status', ['success', 'failed']);
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_requests');
    }
};
