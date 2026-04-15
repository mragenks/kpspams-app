<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengaturan_tarif', function (Blueprint $table) {
            $table->id();
            $table->integer('biaya_beban')->default(5000);
            $table->integer('tarif_batas_bawah')->default(10000)->comment('Harga flat untuk 0-10 m3');
            $table->integer('tarif_11_20')->default(2200)->comment('Harga per m3 untuk 11-20 m3');
            $table->integer('tarif_21_30')->default(2500)->comment('Harga per m3 untuk 21-30 m3');
            $table->integer('tarif_atas_30')->default(3000)->comment('Harga per m3 untuk > 30 m3');
            $table->timestamps();
        });

        // Insert data default agar aplikasi tidak error saat pertama jalan
        DB::table('pengaturan_tarif')->insert([
            'biaya_beban' => 5000,
            'tarif_batas_bawah' => 10000,
            'tarif_11_20' => 2200,
            'tarif_21_30' => 2500,
            'tarif_atas_30' => 3000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_tarif');
    }
};
