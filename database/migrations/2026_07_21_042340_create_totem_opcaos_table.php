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
        Schema::create('totem_opcoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('totem_id')->constrained('totens')->onDelete('cascade');
            $table->string('nome');
            $table->string('codigo')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('totem_opcoes');
    }
};
