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
        Schema::table('arus_kas', function (Blueprint $table) {
            $table->string('foto_bukti')->nullable()->after('no_bukti');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('arus_kas', function (Blueprint $table) {
            $table->dropColumn('foto_bukti');
        });
    }
};
