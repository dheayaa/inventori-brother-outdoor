<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alat', function (Blueprint $table) {
            $table->enum('jenis_alat', ['sewa', 'aksesoris', 'jual'])->default('sewa')->after('ukuran');
            $table->unsignedInteger('harga_jual')->nullable()->after('harga_sewa');
        });

        Schema::table('detail_rental', function (Blueprint $table) {
            $table->enum('jenis_item', ['sewa', 'gratis', 'jual'])->default('sewa')->after('jumlah');
        });
    }

    public function down(): void
    {
        Schema::table('detail_rental', function (Blueprint $table) {
            $table->dropColumn('jenis_item');
        });

        Schema::table('alat', function (Blueprint $table) {
            $table->dropColumn(['jenis_alat', 'harga_jual']);
        });
    }
};
