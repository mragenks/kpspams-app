<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tagihan_air', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->onDelete('cascade');
            $table->foreignId('pencatatan_meter_id')->constrained('pencatatan_meter')->onDelete('cascade');
            $table->integer('bulan_periode');
            $table->integer('tahun_periode');
            $table->decimal('biaya_beban', 10, 2)->default(5000); // fixed monthly fee
            $table->decimal('biaya_pemakaian', 10, 2);
            $table->decimal('total_tagihan', 10, 2);
            $table->decimal('total_terbayar', 10, 2)->default(0);
            $table->decimal('sisa_tagihan', 10, 2);
            $table->enum('status_bayar', ['belum_lunas', 'mencicil', 'lunas'])->default('belum_lunas');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihan_air');
    }
};