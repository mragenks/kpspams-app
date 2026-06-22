<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pencatatan_meter', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pelanggan_id')->constrained('pelanggan')->onDelete('cascade');
            $table->integer('bulan_periode');
            $table->integer('tahun_periode');
            $table->decimal('meter_awal', 10, 2);
            $table->decimal('meter_akhir', 10, 2);
            $table->decimal('volume_m3', 10, 2)->storedAs('meter_akhir - meter_awal');
            $table->date('tanggal_pencatatan');
            $table->foreignId('dicatat_oleh')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['pelanggan_id', 'bulan_periode', 'tahun_periode']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pencatatan_meter');
    }
};