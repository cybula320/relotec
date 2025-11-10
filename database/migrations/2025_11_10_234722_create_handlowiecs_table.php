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
        Schema::create('handlowcy', function (Blueprint $table) {
            $table->id();
            $table->foreignId('firma_id')->constrained('firmy')->onDelete('cascade');
            $table->string('imie');
            $table->string('nazwisko');
            $table->string('email')->unique();
            $table->string('telefon')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('handlowcy');
    }
};
