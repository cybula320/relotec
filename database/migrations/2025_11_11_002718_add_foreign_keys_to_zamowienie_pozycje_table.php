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
        Schema::table('zamowienie_pozycje', function (Blueprint $table) {
            $table->foreign('zamowienie_id')
            ->references('id')->on('zamowienia')
            ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('zamowienie_pozycje', function (Blueprint $table) {
            //
            $table->dropForeign(['zamowienie_id']);

        });
    }
};
