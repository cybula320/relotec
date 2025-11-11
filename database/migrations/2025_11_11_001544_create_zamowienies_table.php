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
        Schema::create('zamowienia', function (Blueprint $table) {
            $table->id();
            $table->string('numer')->nullable()->index();
            $table->foreignId('firma_id')->nullable()->constrained('firmy')->nullOnDelete();
            $table->foreignId('handlowiec_id')->nullable()->constrained('handlowcy')->nullOnDelete();
            $table->string('waluta')->default('PLN');
            $table->integer('payment_terms_days')->nullable();
            $table->date('due_date')->nullable();
            $table->text('uwagi')->nullable();
            $table->decimal('total_net', 15, 2)->default(0);
            $table->decimal('total_gross', 15, 2)->default(0);
            $table->enum('status', ['new', 'in_progress', 'completed', 'cancelled'])->default('new');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('zamowienia');
    }
};
