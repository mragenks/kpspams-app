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
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('Admin yang mengubah tarif');
            $table->integer('biaya_beban')->default(5000);
            $table->integer('tarif_0_5')->default(10000)->comment('Harga flat untuk 0-5 m3');
            $table->integer('tarif_6_10')->default(10000)->comment('Harga flat untuk 6-10 m3');
            $table->integer('tarif_11_20')->default(2200)->comment('Harga per m3 untuk 11-20 m3');
            $table->integer('tarif_21_30')->default(2500)->comment('Harga per m3 untuk 21-30 m3');
            $table->integer('tarif_atas_30')->default(3000)->comment('Harga per m3 untuk > 30 m3');
            $table->timestamps();
        });

        DB::table('pengaturan_tarif')->insert([
            'biaya_beban' => 5000,
            'tarif_0_5'   => 10000,
            'tarif_6_10'  => 10000,
            'tarif_11_20' => 2200,
            'tarif_21_30' => 2500,
            'tarif_atas_30' => 3000,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('pengaturan_tarif');
    }
};
