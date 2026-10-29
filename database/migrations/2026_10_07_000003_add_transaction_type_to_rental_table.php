<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental', function (Blueprint $table) {
            $table->string('jenis_transaksi', 20)->default('langsung')->after('status');
        });

        DB::table('rental')
            ->whereDate('tgl_rental', '>', now()->toDateString())
            ->where('status', '!=', 'dikembalikan')
            ->update(['jenis_transaksi' => 'booking']);
    }

    public function down(): void
    {
        Schema::table('rental', function (Blueprint $table) {
            $table->dropColumn('jenis_transaksi');
        });
    }
};
