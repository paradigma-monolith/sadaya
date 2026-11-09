<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ShipHealthSurveillance extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'ship_health_surveillances';

    protected $fillable = [
        'registration_no',
        'nama_kapal',
        'datang_dari',
        'origin_country',
        'pelabuhan',
        'gross_tonnage',
        'jml_crew',
        'negara_singgah_14_hari',
        'kategori',
        'questions',
        'skoring',
        'kresiko',
        'hasil_status',
        'surat_pernyataan_nahkoda',
        'dokumen_mdh',
        'dokumen_pendukung',
        'estimasi_pnbp',
        'status_verifikasi',
        'catatan_verifikasi',
        'verified_by',
        'verified_at',
        'verifier_name',
        'tanggal_kedatangan',
    ];

    protected $casts = [
        'tanggal_kedatangan' => 'date:Y-m-d',
        'gross_tonnage' => 'integer',
        'jml_crew' => 'integer',
        'skoring' => 'integer',
        'estimasi_pnbp' => 'float',
        'questions' => 'array',
        'dokumen_pendukung' => 'array',
        'verified_at' => 'datetime',
    ];

    /**
     * Daftar 19 Standar Pertanyaan Karantina Kesehatan Kedatangan Kapal (IHR / Permenkes)
     */
    public const QUARANTINE_QUESTIONS = [
        1 => 'Negara yang dikunjungi dalam 14 hari terakhir',
        2 => 'Apakah ada orang yang meninggal di atas kapal selama dalam perjalanan yang bukan diakibatkan oleh kecelakaan?',
        3 => 'Apakah dalam perjalanan ada crew dievakuasi karena sakit atau meninggal?',
        4 => 'Apakah di atas kapal selama dalam perjalanan terdapat kasus penyakit yang diduga karena penyakit menular?',
        5 => 'Apakah jumlah orang sakit saat ini lebih banyak dari biasanya?',
        6 => 'Apakah ada orang sakit di atas kapal saat ini?',
        7 => 'Apakah dokter kapal dipanggil untuk mengobati sakit tersebut?',
        8 => 'Apakah ada kondisi di atas kapal yang dapat menyebabkan infeksi atau penyebaran penyakit?',
        9 => 'Apakah dilakukan tindakan penyehatan selama dalam perjalanan (desinfeksi, desinseksi, deratisasi)?',
        10 => 'Apakah ditemukan binatang pembawa penyakit (tikus, pinjal, lalat, nyamuk) di atas kapal?',
        11 => 'Apakah ditemukan orang sakit dengan gejala demam (suhu >= 38 C) disertai batuk/pilek/sesak nafas?',
        12 => 'Apakah ditemukan orang sakit dengan gejala ruam/bintik merah pada kulit?',
        13 => 'Apakah ditemukan orang sakit dengan gejala diare akut?',
        14 => 'Apakah ditemukan orang sakit dengan gejala muntah terus menerus?',
        15 => 'Apakah ditemukan orang sakit dengan gejala penyakit kuning (mata/kulit kuning)?',
        16 => 'Apakah ditemukan orang sakit dengan gejala pembesaran kelenjar leher/ketiak/selangkangan?',
        17 => 'Apakah ditemukan orang sakit dengan pendarahan spontan (hidung, gusi, muntah darah, BAB darah)?',
        18 => 'Apakah ditemukan orang sakit dengan penurunan kesadaran/koma?',
        19 => 'Apakah seluruh ABK memiliki Sertifikat Vaksinasi Internasional (ICV) yang masih berlaku?',
    ];

    /**
     * Hitung tarif PNBP Jasa Kekarantinaan Kesehatan per kapal berdasarkan Gross Tonnage (GT)
     * PP Tarif PNBP Kementerian Kesehatan RI.
     */
    public static function calculatePnbp(int|float $gt): float
    {
        $gt = max(0, (int)$gt);

        if ($gt <= 100) {
            return 50000.0;
        } elseif ($gt <= 200) {
            return 60000.0;
        } elseif ($gt <= 350) {
            return 70000.0;
        } elseif ($gt <= 1000) {
            return 85000.0;
        } elseif ($gt <= 2000) {
            return 120000.0;
        } elseif ($gt <= 3500) {
            return 150000.0;
        } elseif ($gt <= 7000) {
            return 175000.0;
        } elseif ($gt <= 10000) {
            return 200000.0;
        } elseif ($gt <= 15000) {
            return 250000.0;
        } elseif ($gt <= 20000) {
            return 275000.0;
        } else {
            return 300000.0;
        }
    }

    /**
     * Hitung Kategori Risiko dan Rekomendasi Status Berdasarkan Skoring
     */
    public static function calculateRisk(int $score): array
    {
        if ($score >= 20) {
            return [
                'kresiko' => 'TINGGI',
                'hasil_status' => 'ZONA KARANTINA',
                'badge_color' => 'red',
            ];
        } elseif ($score >= 10) {
            return [
                'kresiko' => 'SEDANG',
                'hasil_status' => 'ZONA KARANTINA',
                'badge_color' => 'amber',
            ];
        }

        return [
            'kresiko' => 'RENDAH',
            'hasil_status' => 'SANDAR',
            'badge_color' => 'emerald',
        ];
    }

    /**
     * Relasi ke Verifikator (User)
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    /**
     * Search Scope
     */
    public function scopeSearch($query, ?string $term)
    {
        if (!empty($term)) {
            $query->where(function ($q) use ($term) {
                $q->where('nama_kapal', 'like', "%{$term}%")
                    ->orWhere('registration_no', 'like', "%{$term}%")
                    ->orWhere('datang_dari', 'like', "%{$term}%")
                    ->orWhere('pelabuhan', 'like', "%{$term}%")
                    ->orWhere('origin_country', 'like', "%{$term}%");
            });
        }
    }

    /**
     * Filter Pelabuhan
     */
    public function scopeFilterPelabuhan($query, ?string $pelabuhan)
    {
        if (!empty($pelabuhan)) {
            $query->where('pelabuhan', $pelabuhan);
        }
    }

    /**
     * Filter Kategori Asal (Dalam Negeri / Luar Negeri)
     */
    public function scopeFilterKategori($query, ?string $kategori)
    {
        if (!empty($kategori)) {
            $query->where('kategori', $kategori);
        }
    }

    /**
     * Filter Kategori Risiko (RENDAH, SEDANG, TINGGI)
     */
    public function scopeFilterKresiko($query, ?string $kresiko)
    {
        if (!empty($kresiko)) {
            $query->where('kresiko', strtoupper($kresiko));
        }
    }

    /**
     * Filter Status Verifikasi / Rekomendasi
     */
    public function scopeFilterStatus($query, ?string $status)
    {
        if (!empty($status)) {
            if (in_array(strtoupper($status), ['SANDAR', 'ZONA KARANTINA'])) {
                $query->where('hasil_status', strtoupper($status));
            } else {
                $query->where('status_verifikasi', $status);
            }
        }
    }

    /**
     * Activity Log Options
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'nama_kapal',
                'pelabuhan',
                'gross_tonnage',
                'skoring',
                'kresiko',
                'hasil_status',
                'status_verifikasi',
                'verified_by',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('ship_health_surveillance');
    }
}

