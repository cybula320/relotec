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
        Schema::create('oferta_pozycje', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oferta_id')->constrained('oferty')->cascadeOnDelete();
            $table->string('nazwa');
            $table->text('opis')->nullable();
            $table->integer('ilosc')->default(1);
            $table->decimal('unit_price_net', 15, 2)->default(0);
            $table->decimal('unit_price_gross', 15, 2)->nullable();
            $table->decimal('vat_rate', 5, 2)->default(23);
            $table->string('zdjecie')->nullable();
            $table->text('uwagi')->nullable();
            $table->decimal('total_net', 15, 2)->default(0);
            $table->decimal('total_gross', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oferta_pozycje');
    }
};
