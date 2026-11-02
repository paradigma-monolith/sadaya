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
        Schema::create('ship_sanitation_inspections', function (Blueprint $table) {
            $table->id();
            $table->string('registration_no', 64)->unique()->index();
            $table->string('pelabuhan')->nullable();
            $table->date('tanggal');
            $table->string('sertifikat_mencatat', 50)->default('Pembebasan');
            $table->string('imo_no', 100);
            $table->string('jenis_kapal', 50)->nullable();
            $table->string('detail_jenis_kapal', 50)->nullable();
            $table->string('nama_kapal');
            $table->string('bendera', 100)->nullable();
            $table->unsignedBigInteger('berat_gt')->default(0);
            $table->string('kondisi_palka', 50)->nullable();
            $table->string('nama_pemeriksa');
            $table->text('alamat_pemeriksa')->nullable();
            
            // 32 Areas of WHO IHR Inspection Checklist (JSON array)
            $table->json('areas')->nullable();
            
            // Conclusion & Issuer Details
            $table->string('pejabat_penerbit')->nullable();
            $table->text('keterangan')->nullable();
            $table->longText('tanda_tangan')->nullable();
            
            // Geotagging & Photos
            $table->string('foto_geotagging')->nullable();
            $table->decimal('latitude', 11, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('lokasi_geotag')->nullable();
            
            // Workflow & Verification Status
            $table->string('status', 30)->default('submitted')->index();
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
        Schema::dropIfExists('ship_sanitation_inspections');
    }
};
