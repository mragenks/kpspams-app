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
        Schema::table('pengaturan_tarif', function (Blueprint $table) {
            $table->unsignedBigInteger('diupdate_oleh')->nullable()->after('tarif_atas_30');

            $table->foreign('diupdate_oleh')
                  ->references('id')
                  ->on('pengurus')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaturan_tarif', function (Blueprint $table) {
            $table->dropForeign(['diupdate_oleh']);
            $table->dropColumn('diupdate_oleh');
        });
    }
};