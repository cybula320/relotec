<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('zamowienie_pozycje', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('zamowienie_id');
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

    public function down(): void
    {
        Schema::dropIfExists('zamowienie_pozycje');
    }
};