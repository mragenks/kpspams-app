<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Mengubah ENUM untuk menambahkan 'pengembalian'
        DB::statement("ALTER TABLE riwayat_inventaris MODIFY COLUMN jenis_mutasi ENUM('masuk', 'keluar', 'pengembalian') NOT NULL");
    }

    public function down()
    {
        // Mengembalikan ke kondisi semula jika di-rollback
        DB::statement("ALTER TABLE riwayat_inventaris MODIFY COLUMN jenis_mutasi ENUM('masuk', 'keluar') NOT NULL");
    }
};
