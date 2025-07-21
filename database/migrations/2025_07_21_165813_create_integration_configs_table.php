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
        Schema::create('integration_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('iddepositante');
            $table->string('client_name');
            $table->string('client_doc');
            $table->string('tms_cd_doc');
            $table->string('tms_cd_id');
            $table->string('endpoint');
            $table->string('headers');
            $table->string('production_token');
            $table->string('test_token');
            $table->text('body');
            $table->text('default_options');
            $table->string('http_method');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_configs');
    }
};
