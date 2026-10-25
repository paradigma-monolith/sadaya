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
        Schema::create('aircraft_sanitation_inspections', function (Blueprint $table) {
            $table->id();
            $table->string('registration_no', 64)->unique()->index();
            $table->string('pemilik'); // Owner or Operator
            $table->string('tanda_kebangsaan'); // Marks of Nationality and Registration
            $table->date('tanggal');
            $table->string('waktu', 10);
            
            // Inspection check items (JSON structures)
            $table->json('kabin_penumpang')->nullable();
            $table->json('kompartemen_toilet')->nullable();
            $table->json('galley')->nullable();
            $table->json('dokumen_kesehatan')->nullable();
            $table->string('pembuangan_kotoran', 20)->default('ya');
            $table->json('penanganan_air')->nullable();
            $table->json('keadaan_makanan')->nullable();
            $table->json('binatang_pengganggu')->nullable();
            $table->string('surat_sanitasi', 20)->default('ada');
            
            // Conclusion & Measures
            $table->string('kesimpulan', 20)->default('layak'); // layak / tidak_layak
            $table->string('tindakan', 40)->nullable(); // disinseksi, fumigasi, disinfeksi, general_cleaning
            $table->text('catatan_rekomendasi')->nullable();
            
            // Signatures & Officers
            $table->string('petugas_kabin_nama');
            $table->longText('petugas_kabin_ttd')->nullable();
            $table->string('petugas_bkk_nama');
            $table->longText('petugas_bkk_ttd')->nullable();
            
            // Workflow & Verification Status
            $table->string('status', 30)->default('submitted')->index(); // submitted, verified, approved, rejected
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
        Schema::dropIfExists('aircraft_sanitation_inspections');
    }
};
