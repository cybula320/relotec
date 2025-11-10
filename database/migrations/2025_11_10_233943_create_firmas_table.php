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
        Schema::create('firmy', function (Blueprint $table) {
            $table->id();
            $table->string('nazwa');
            $table->string('nip')->nullable();
            $table->string('email')->nullable();
            $table->string('telefon')->nullable();
            $table->string('adres')->nullable();
            $table->string('miasto')->nullable();
            $table->text('uwagi')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('firmy');
    }
};
