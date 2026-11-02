<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kantor_wilkers', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('no')->default(1)->index();
            $table->string('kode', 30)->unique();
            $table->string('nama', 255);
            $table->string('nama_pendek', 100)->nullable();
            $table->string('tipe', 50)->default('Wilayah Kerja'); // Kantor Induk, Pos Bandara, Wilayah Kerja
            $table->string('moda', 50)->nullable(); // Pusat Komando, Udara, Laut & Pesisir, dll
            $table->string('zona', 100)->nullable(); // Kota/Kabupaten
            $table->text('alamat');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('is_induk')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kantor_wilkers');
    }
};
