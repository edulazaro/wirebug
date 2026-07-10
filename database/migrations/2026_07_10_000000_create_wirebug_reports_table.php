<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(config('wirebug.table', 'wirebug_reports'), function (Blueprint $table) {
            $table->id();

            // Sin FK: el paquete no asume el nombre de la tabla de usuarios.
            $table->unsignedBigInteger('user_id')->nullable()->index();

            $table->string('type', 32)->index();
            $table->text('message');
            $table->string('email')->nullable();

            // Contexto capturado automáticamente (config 'capture_context').
            $table->string('url', 2048)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('locale', 12)->nullable();
            $table->json('meta')->nullable();

            $table->string('status', 32)->default('new')->index();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(config('wirebug.table', 'wirebug_reports'));
    }
};
