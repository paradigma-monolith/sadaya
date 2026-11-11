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
     * Daftar negara (berdasarkan form selfassessment.bkkbandung.net/quarantine/english)
     * Nilai boolean menandakan zona merah / terjangkit wabah (true = +20 skor).
     */
    public const QUARANTINE_COUNTRIES = [
        'Afghanistan' => true,
        'Albania' => true,
        'Algeria' => true,
        'Andorra' => false,
        'Angola' => true,
        'Antigua and Barbuda' => false,
        'Argentina' => true,
        'Armenia' => true,
        'Australia' => true,
        'Austria' => true,
        'Azerbaijan' => true,
        'Bahamas' => false,
        'Bahrain' => true,
        'Bangladesh' => true,
        'Barbados' => false,
        'Belarus' => false,
        'Belgium' => true,
        'Belize' => false,
        'Benin' => true,
        'Bhutan' => false,
        'Bolivia' => true,
        'Bosnia and Herzegovina' => false,
        'Botswana' => false,
        'Brazil' => true,
        'Brunei Darussalam' => false,
        'Bulgaria' => true,
        'Burkina Faso' => true,
        'Burundi' => true,
        'Cabo Verde' => false,
        'Cambodia' => true,
        'Cameroon' => true,
        'Canada' => true,
        'Central African Republic' => true,
        'Chad' => true,
        'Chile' => true,
        'China' => true,
        'Colombia' => true,
        'Comoros' => false,
        'Congo' => false,
        'Costa Rica' => true,
        'Cote d\'Ivoire' => true,
        'Croatia' => true,
        'Cuba' => true,
        'Cyprus' => true,
        'Czechia' => true,
        'Democratic Republic of the Congo' => true,
        'Denmark' => true,
        'Djibouti' => true,
        'Dominica' => false,
        'Dominican Republic' => false,
        'Ecuador' => true,
        'Egypt' => false,
        'El Salvador' => false,
        'Equatorial Guinea' => true,
        'Eritrea' => false,
        'Estonia' => false,
        'Eswatini' => false,
        'Ethiopia' => true,
        'Federated States of Micronesia' => false,
        'Fiji' => false,
        'Finland' => true,
        'France' => true,
        'Gabon' => true,
        'Gambia' => true,
        'Georgia' => false,
        'Germany' => true,
        'Ghana' => true,
        'Greece' => true,
        'Grenada' => false,
        'Guatemala' => true,
        'Guinea' => true,
        'Guinea-Bissau' => true,
        'Guyana' => true,
        'Haiti' => false,
        'Honduras' => true,
        'Hungary' => true,
        'Iceland' => false,
        'India' => true,
        'Indonesia' => false,
        'Iran' => false,
        'Iraq' => false,
        'Ireland' => true,
        'Israel' => true,
        'Italy' => true,
        'Jamaica' => true,
        'Japan' => true,
        'Jordan' => false,
        'Kazakhstan' => false,
        'Kenya' => true,
        'Kiribati' => false,
        'Kuwait' => true,
        'Kyrgyzstan' => false,
        'Laos' => false,
        'Latvia' => false,
        'Lebanon' => true,
        'Lesotho' => false,
        'Liberia' => true,
        'Libya' => false,
        'Liechtenstein' => false,
        'Lithuania' => true,
        'Luxembourg' => true,
        'Madagascar' => false,
        'Malawi' => true,
        'Malaysia' => true,
        'Maldives' => false,
        'Mali' => true,
        'Malta' => true,
        'Marshall Islands' => false,
        'Mauritania' => true,
        'Mauritius' => true,
        'Mexico' => true,
        'Monaco' => false,
        'Mongolia' => true,
        'Montenegro' => false,
        'Morocco' => true,
        'Mozambique' => true,
        'Myanmar' => true,
        'Namibia' => false,
        'Nauru' => false,
        'Nepal' => true,
        'Netherlands' => true,
        'New Zealand' => true,
        'Nicaragua' => false,
        'Niger' => true,
        'Nigeria' => true,
        'North Macedonia' => true,
        'Norway' => true,
        'Oman' => true,
        'Pakistan' => true,
        'Palau' => false,
        'Palestine' => true,
        'Panama' => true,
        'Papua New Guinea' => true,
        'Paraguay' => true,
        'Peru' => true,
        'Poland' => true,
        'Portugal' => true,
        'Qatar' => true,
        'Republic of Korea' => false,
        'Republic of Moldova' => false,
        'Romania' => true,
        'Russian Federation' => false,
        'Rwanda' => true,
        'Saint Kitts and Nevis' => false,
        'Saint Lucia' => false,
        'Saint Vincent and the Grenadines' => false,
        'Samoa' => false,
        'San Marino' => false,
        'Sao Tome and Principe' => false,
        'Saudi Arabia' => true,
        'Senegal' => true,
        'Serbia' => true,
        'Seychelles' => false,
        'Sierra Leone' => true,
        'Singapore' => true,
        'Slovakia' => true,
        'Slovenia' => true,
        'Solomon Islands' => false,
        'Somalia' => false,
        'South Africa' => true,
        'South Korea' => true,
        'South Sudan' => true,
        'Spain' => true,
        'Sri Lanka' => true,
        'Sudan' => true,
        'Suriname' => true,
        'Sweden' => true,
        'Switzerland' => true,
        'Syrian Arab Republic' => false,
        'Tajikistan' => false,
        'Thailand' => true,
        'The Philippines' => true,
        'Timor-Leste' => false,
        'Togo' => true,
        'Tonga' => false,
        'Trinidad and Tobago' => true,
        'Tunisia' => false,
        'Turkmenistan' => false,
        'Tuvalu' => false,
        'Türkiye' => false,
        'Uganda' => true,
        'Ukraine' => false,
        'United Arab Emirates' => true,
        'United Kingdom' => true,
        'United Republic of Tanzania' => false,
        'United States' => false,
        'Uruguay' => true,
        'Uzbekistan' => false,
        'Vanuatu' => false,
        'Vatican' => false,
        'Venezuela (Bolivarian Republic o' => false,
        'Viet Nam' => false,
        'Yemen' => true,
        'Zambia' => true,
        'Zimbabwe' => true,
    ];

    /**
     * Cek apakah terdapat negara zona merah dalam daftar negara yang dikunjungi.
     */
    public static function hasRedZoneCountry(array $countries): bool
    {
        foreach ($countries as $country) {
            if ((self::QUARANTINE_COUNTRIES[$country] ?? false) === true) {
                return true;
            }
        }

        return false;
    }

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

