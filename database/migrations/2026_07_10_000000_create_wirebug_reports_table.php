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

            // Quién reporta, polimórfico: cada app decide su modelo (User,
            // Client...). Respeta el morph map. Null = invitado.
            $table->nullableMorphs('reporter');

            $table->string('type', 32)->index();
            $table->text('message');
            $table->text('steps')->nullable();
            $table->string('email')->nullable();

            // Rutas en el disco configurado en 'wirebug.uploads.disk'.
            $table->string('screenshot_path', 2048)->nullable();
            $table->string('recording_path', 2048)->nullable();

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
