<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('oferty', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_oferta_id')->nullable()->after('payment_method_id');
            $table->string('correction_letter', 5)->nullable()->after('parent_oferta_id');

            $table->foreign('parent_oferta_id')
                ->references('id')
                ->on('oferty')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('oferty', function (Blueprint $table) {
            $table->dropForeign(['parent_oferta_id']);
            $table->dropColumn(['parent_oferta_id', 'correction_letter']);
        });
    }
};
