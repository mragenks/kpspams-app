<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('pengurus', function (Blueprint $table) {
            if (!Schema::hasColumn('pengurus', 'no_hp')) {
                $table->string('no_hp', 20)->nullable()->after('jabatan');
            }

            if (!Schema::hasColumn('pengurus', 'alamat')) {
                $table->text('alamat')->nullable()->after('no_hp');
            }
        });
    }

    public function down()
    {
        Schema::table('pengurus', function (Blueprint $table) {
            if (Schema::hasColumn('pengurus', 'no_hp')) {
                $table->dropColumn('no_hp');
            }

            if (Schema::hasColumn('pengurus', 'alamat')) {
                $table->dropColumn('alamat');
            }
        });
    }
};
