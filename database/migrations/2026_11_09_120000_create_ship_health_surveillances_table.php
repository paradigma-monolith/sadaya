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
        Schema::create('ship_health_surveillances', function (Blueprint $table) {
            $table->id();
            $table->string('registration_no', 64)->unique()->index();
            $table->string('nama_kapal')->index();
            $table->string('datang_dari');
            $table->string('origin_country', 100)->nullable();
            $table->string('pelabuhan')->index();
            $table->unsignedBigInteger('gross_tonnage')->default(0)->index();
            $table->integer('jml_crew')->default(0);
            $table->string('negara_singgah_14_hari')->nullable();
            $table->string('kategori', 30)->default('Dalam Negeri')->index(); // 'Dalam Negeri' or 'Luar Negeri'
            
            // Health & Quarantine Questionnaire & Risk Assessment
            $table->json('questions')->nullable();
            $table->integer('skoring')->default(0);
            $table->string('kresiko', 20)->default('RENDAH')->index(); // 'RENDAH', 'SEDANG', 'TINGGI'
            $table->string('hasil_status', 30)->default('SANDAR')->index(); // 'SANDAR', 'ZONA KARANTINA'
            
            // Documents Upload
            $table->string('surat_pernyataan_nahkoda')->nullable();
            $table->string('dokumen_mdh')->nullable();
            $table->json('dokumen_pendukung')->nullable();
            
            // Financial & PNBP Tariff Calculation
            $table->decimal('estimasi_pnbp', 12, 2)->default(0);
            
            // Verification Workflow
            $table->string('status_verifikasi', 30)->default('pending')->index(); // 'pending', 'verified', 'rejected'
            $table->text('catatan_verifikasi')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->string('verifier_name')->nullable();
            
            // Dates
            $table->date('tanggal_kedatangan')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ship_health_surveillances');
    }
};

