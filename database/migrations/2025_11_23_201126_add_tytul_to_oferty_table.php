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
        Schema::table('oferty', function (Blueprint $table) {
            $table->string('tytul', 255)->nullable()->after('numer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('oferty', function (Blueprint $table) {
            $table->dropColumn('tytul');
        });
    }
};
