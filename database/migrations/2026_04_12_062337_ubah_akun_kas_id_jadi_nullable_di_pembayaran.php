<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            // Mengubah kolom menjadi nullable agar bisa diisi null saat status pending
            $table->unsignedBigInteger('akun_kas_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pembayaran', function (Blueprint $table) {
            $table->unsignedBigInteger('akun_kas_id')->nullable(false)->change();
        });
    }
};
