<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('arus_kas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('akun_kas_id')->constrained('akun_kas')->onDelete('cascade');
            $table->enum('jenis_transaksi', ['masuk', 'keluar', 'mutasi_masuk', 'mutasi_keluar']);
            $table->decimal('nominal', 15, 2);
            $table->string('keterangan');
            $table->string('no_bukti')->nullable();
            $table->date('tanggal');
            $table->foreignId('dicatat_oleh')->constrained('users')->onDelete('cascade');
            $table->foreignId('pembayaran_id')->nullable()->constrained('pembayaran')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arus_kas');
    }
};