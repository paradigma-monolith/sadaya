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
        Schema::create('flight_medical_inspections', function (Blueprint $table) {
            $table->id();
            $table->string('registration_no', 64)->unique()->index();
            $table->enum('type', ['alat', 'obat'])->index(); // 'alat' = Alkes P3K & Emergensi, 'obat' = Obat P3K & Emergensi
            $table->string('penerbangan_haji_h', 20)->nullable(); // e.g. 1447 H
            $table->string('penerbangan_haji_m', 20)->nullable(); // e.g. 2026 M
            $table->string('embarkasi', 100);
            $table->string('nomor_kloter', 100);
            $table->string('no_kontak', 50)->nullable();
            
            // JSON items for P3K and Emergency kit
            $table->json('items_p3k')->nullable();
            $table->json('items_emergensi')->nullable();
            
            // Documentation & Geotagging
            $table->string('foto_geotagging')->nullable();
            $table->decimal('latitude', 11, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('lokasi_geotag')->nullable();
            
            // Dual Signatures (Awak Kabin & Petugas Karantina BKK)
            $table->string('crew_nama');
            $table->longText('crew_ttd')->nullable();
            $table->string('pelapor_nama');
            $table->longText('pelapor_ttd')->nullable();
            
            // Workflow Status & Official Notes
            $table->string('status', 30)->default('submitted')->index();
            $table->text('keterangan')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flight_medical_inspections');
    }
};
