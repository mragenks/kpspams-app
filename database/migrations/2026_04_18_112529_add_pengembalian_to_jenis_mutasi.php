<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE riwayat_inventaris MODIFY COLUMN jenis_mutasi ENUM('masuk', 'keluar', 'pengembalian') NOT NULL");
    }

    public function down()
    {
        DB::statement("ALTER TABLE riwayat_inventaris MODIFY COLUMN jenis_mutasi ENUM('masuk', 'keluar') NOT NULL");
    }
};
