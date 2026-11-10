<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations for PRL (Pengendalian Risiko Lingkungan) module.
     */
    public function up(): void
    {
        // 1. Master Tempat Pengelolaan Pangan (TPP)
        Schema::create('prl_tpp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->string('wilker')->nullable()->index();
            $table->string('nama_tpp')->index();
            $table->string('penanggung_jawab')->nullable();
            $table->string('jml_karyawan')->nullable();
            $table->string('kapasitas_produksi')->nullable();
            $table->string('created_by_name')->nullable();
            $table->string('updated_by_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Inspeksi Higiene Sanitasi TPP
        Schema::create('prl_inspeksi_tpp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->foreignId('prl_tpp_id')->nullable()->constrained('prl_tpp')->nullOnDelete();
            $table->string('wilker')->nullable()->index();
            $table->date('tanggal_inspeksi')->index();
            $table->string('nama_tpp')->index();
            $table->string('penanggung_jawab_tpp')->nullable();
            $table->string('jml_karyawan_tpp')->nullable();
            $table->string('kapasitas_produksi_tpp')->nullable();
            $table->string('nama_pemeriksa')->nullable();
            
            // Checklist & Scores
            $table->string('sec_a_satu', 10)->nullable();
            $table->string('sec_a_dua', 10)->nullable();
            $table->string('sec_a_tiga', 10)->nullable();
            $table->integer('sec_a_skor')->default(0);
            
            $table->string('sec_b_satu', 10)->nullable();
            $table->string('sec_b_dua', 10)->nullable();
            $table->string('sec_b_tiga', 10)->nullable();
            $table->integer('sec_b_skor')->default(0);
            
            $table->integer('sec_c_skor')->default(0);
            $table->string('sec_c_keterangan')->nullable();
            
            $table->integer('sec_d_nilai_inspeksi_sebelumnya')->default(0);
            $table->integer('sec_d_skor')->default(0);
            
            $table->integer('hasil')->default(0); // Total Skor
            $table->string('tingkat_risiko', 20)->default('Rendah')->index(); // Rendah, Sedang, Tinggi
            $table->text('kesimpulan')->nullable();
            $table->text('makanan')->nullable();
            $table->text('saran_rencana_tindakan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. Master Sarana Air Bersih (SAB)
        Schema::create('prl_sab', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->string('wilker')->nullable()->index();
            $table->string('nama_sab')->index();
            $table->string('penanggung_jawab')->nullable();
            $table->string('penanggung_jawab_no_hp', 30)->nullable();
            $table->string('sumber_air')->nullable();
            $table->string('kapasitas_penampungan')->nullable();
            $table->string('created_by_name')->nullable();
            $table->string('updated_by_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Inspeksi Sanitasi Sarana Air Bersih (SAB)
        Schema::create('prl_inspeksi_sab', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->foreignId('prl_sab_id')->nullable()->constrained('prl_sab')->nullOnDelete();
            $table->string('wilker')->nullable()->index();
            $table->date('tanggal_inspeksi')->index();
            $table->string('nama_sab')->index();
            $table->string('penanggung_jawab_sab')->nullable();
            $table->string('penanggung_jawab_no_hp_sab', 30)->nullable();
            $table->string('sumber_air_sab')->nullable();
            $table->string('kapasitas_penampungan_sab')->nullable();
            $table->string('nama_pemeriksa')->nullable();
            
            // Section B (4 Pertanyaan)
            $table->string('sec_b_satu', 10)->nullable();
            $table->string('sec_b_dua', 10)->nullable();
            $table->string('sec_b_tiga', 10)->nullable();
            $table->string('sec_b_empat', 10)->nullable();
            
            // Section C (10 Pertanyaan)
            $table->string('sec_c_satu', 10)->nullable();
            $table->string('sec_c_dua', 10)->nullable();
            $table->string('sec_c_tiga', 10)->nullable();
            $table->string('sec_c_empat', 10)->nullable();
            $table->string('sec_c_lima', 10)->nullable();
            $table->string('sec_c_enam', 10)->nullable();
            $table->string('sec_c_tujuh', 10)->nullable();
            $table->string('sec_c_delapan', 10)->nullable();
            $table->string('sec_c_sembilan', 10)->nullable();
            $table->string('sec_c_sepuluh', 10)->nullable();
            
            $table->integer('hasil')->default(0); // Total Skor
            $table->string('tingkat_risiko', 20)->default('Rendah')->index(); // Rendah, Sedang, Tinggi
            $table->text('saran_rencana_tindakan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Master Higiene Sanitasi Bangunan dan Usaha (HSBU)
        Schema::create('prl_hsbu', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->string('wilker')->nullable()->index();
            $table->string('nama_gedung')->index();
            $table->string('penanggung_jawab')->nullable();
            $table->string('jml_karyawan')->nullable();
            $table->string('created_by_name')->nullable();
            $table->string('updated_by_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 6. Inspeksi Higiene Sanitasi Bangunan dan Usaha (HSBU)
        Schema::create('prl_inspeksi_hsbu', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->foreignId('prl_hsbu_id')->nullable()->constrained('prl_hsbu')->nullOnDelete();
            $table->string('wilker')->nullable()->index();
            $table->date('tanggal_inspeksi')->index();
            $table->string('nama_gedung')->index();
            $table->string('penanggung_jawab_hsbu')->nullable();
            $table->string('jml_karyawan_hsbu')->nullable();
            $table->string('nama_pemeriksa')->nullable();
            
            // Detail Komponen Penilaian (JSON)
            $table->json('komponen_penilaian')->nullable();
            
            $table->decimal('total', 8, 2)->default(0); // Total Skor
            $table->string('tingkat_risiko', 20)->default('Rendah')->index(); // Rendah, Sedang, Tinggi
            $table->text('saran_rencana_tindakan')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 7. Surveilans Vektor dan Reservoar (SIVERA)
        Schema::create('prl_sivera_vektor', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('source_id')->nullable()->index();
            $table->string('jenis_vektor', 50)->index(); // Jentik Aedes, Lalat, Tikus, Kecoa, Pinjal, Anopheles
            $table->string('wilker')->nullable()->index();
            $table->string('lokasi')->nullable()->index();
            $table->date('tanggal')->nullable()->index();
            $table->string('parameter_utama')->nullable(); // e.g. ABJ, Fly Index, Trap Success
            $table->decimal('nilai_indeks', 8, 2)->default(0);
            $table->string('baku_mutu_status', 30)->default('Memenuhi Syarat')->index(); // Memenuhi Syarat / Di Atas Baku Mutu
            $table->json('detail_data')->nullable();
            $table->text('tindakan_pengendalian')->nullable();
            $table->string('created_by_name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prl_sivera_vektor');
        Schema::dropIfExists('prl_inspeksi_hsbu');
        Schema::dropIfExists('prl_hsbu');
        Schema::dropIfExists('prl_inspeksi_sab');
        Schema::dropIfExists('prl_sab');
        Schema::dropIfExists('prl_inspeksi_tpp');
        Schema::dropIfExists('prl_tpp');
    }
};

