<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventaris', function (Blueprint $table) {
            $table->id();
            $table->string('kode_barang')->unique();
            $table->string('nama_barang');
            $table->enum('jenis_aset', ['tetap', 'tidak_tetap']); // fixed vs consumable
            $table->integer('stok_awal')->default(0);
            $table->integer('stok_saat_ini')->default(0);
            $table->enum('kondisi', ['baik', 'rusak', 'perlu_perbaikan'])->default('baik');
            $table->string('lokasi')->nullable();
            $table->date('tanggal_perolehan')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventaris');
    }
};