<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pelanggan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('kode_pelanggan')->unique(); // e.g. PLG-001
            $table->string('nama_pelanggan');
            $table->string('nik', 16)->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_hp')->nullable();
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->decimal('meteran_awal', 10, 2)->default(0); // starting meter reading
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pelanggan');
    }
};