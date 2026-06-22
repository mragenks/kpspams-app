<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'nama_lengkap')) {
                $table->string('nama_lengkap')->after('id')->nullable();
            }
            if (!Schema::hasColumn('users', 'no_hp')) {
                $table->string('no_hp', 20)->after('password')->nullable();
            }
            if (!Schema::hasColumn('users', 'alamat')) {
                $table->text('alamat')->after('no_hp')->nullable();
            }
        });

        DB::statement('UPDATE users u JOIN pelanggan p ON u.id = p.user_id SET u.nama_lengkap = p.nama_pelanggan, u.no_hp = p.no_hp, u.alamat = p.alamat');

        DB::statement('UPDATE users u JOIN pengurus p ON u.id = p.user_id SET u.nama_lengkap = p.nama_pengurus, u.no_hp = p.no_hp, u.alamat = p.alamat');

        // Untuk berjaga-jaga jika ada user (Admin) yang belum punya data di tabel pengurus, set namanya sama dengan username
        DB::statement('UPDATE users SET nama_lengkap = username WHERE nama_lengkap IS NULL');

        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropColumn(['nama_pelanggan', 'no_hp', 'alamat']);
        });

        Schema::table('pengurus', function (Blueprint $table) {
            $table->dropColumn(['nama_pengurus', 'no_hp', 'alamat']);
        });

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->enum('status_validasi', ['pending', 'disetujui', 'ditolak'])->default('disetujui')->after('foto_bukti');
        });

        DB::statement("UPDATE pembayaran SET status_validasi = 'pending' WHERE is_pending = 1");

        Schema::table('pembayaran', function (Blueprint $table) {
            $table->dropColumn('is_pending');
        });

        Schema::table('arus_kas', function (Blueprint $table) {
            $table->dropForeign(['pembayaran_id']);

            $table->foreign('pembayaran_id')
                ->references('id')
                ->on('pembayaran')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        //
    }
};
