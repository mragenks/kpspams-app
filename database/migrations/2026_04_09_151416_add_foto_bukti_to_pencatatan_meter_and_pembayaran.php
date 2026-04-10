<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pencatatan_meter', function (Blueprint $table) {
            $table->string('foto_meteran')->nullable()->after('tanggal_pencatatan');
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->string('foto_bukti')->nullable()->after('keterangan');
        });
    }

    public function down(): void
    {
        Schema::table('pencatatan_meter', function (Blueprint $table) {
            $table->dropColumn('foto_meteran');
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn('foto_bukti');
        });
    }
};
