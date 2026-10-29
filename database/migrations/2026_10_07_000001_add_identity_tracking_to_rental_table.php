<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental', function (Blueprint $table) {
            $table->boolean('identitas_dititipkan')->default(false)->after('denda');
            $table->boolean('identitas_dikembalikan')->default(false)->after('identitas_dititipkan');
        });
    }

    public function down(): void
    {
        Schema::table('rental', function (Blueprint $table) {
            $table->dropColumn(['identitas_dititipkan', 'identitas_dikembalikan']);
        });
    }
};
