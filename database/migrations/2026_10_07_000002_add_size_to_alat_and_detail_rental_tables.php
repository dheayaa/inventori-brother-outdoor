<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alat', function (Blueprint $table) {
            $table->string('ukuran', 50)->nullable()->after('nama_alat');
        });

        Schema::table('detail_rental', function (Blueprint $table) {
            $table->string('ukuran', 50)->nullable()->after('jumlah');
        });
    }

    public function down(): void
    {
        Schema::table('detail_rental', function (Blueprint $table) {
            $table->dropColumn('ukuran');
        });

        Schema::table('alat', function (Blueprint $table) {
            $table->dropColumn('ukuran');
        });
    }
};
