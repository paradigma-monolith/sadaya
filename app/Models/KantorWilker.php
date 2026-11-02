<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KantorWilker extends Model
{
    use HasFactory;

    protected $table = 'kantor_wilkers';

    protected $fillable = [
        'no',
        'kode',
        'nama',
        'nama_pendek',
        'tipe',
        'moda',
        'zona',
        'alamat',
        'latitude',
        'longitude',
        'is_induk',
        'is_active',
    ];

    protected $casts = [
        'no' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
        'is_induk' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Fallback standard 7 offices if table is empty or during bootstrapping.
     */
    public const DEFAULT_OFFICES = [
        [
            'no' => 1,
            'kode' => 'BKK-BDG-01',
            'nama' => 'Balai Kekarantinaan Kesehatan Kelas I Bandung (Induk)',
            'nama_pendek' => 'Induk BKK Bandung',
            'tipe' => 'Kantor Induk',
            'moda' => 'Pusat Komando',
            'zona' => 'Kota Bandung',
            'alamat' => 'Jl. Cikapayang No.5, Tamansari, Kec. Bandung Wetan, Kota Bandung, Jawa Barat 40116',
            'latitude' => -6.8989,
            'longitude' => 107.6108,
            'is_induk' => true,
            'is_active' => true,
        ],
        [
            'no' => 2,
            'kode' => 'POS-BDO-02',
            'nama' => 'POS Bandara Husen Sastranegara',
            'nama_pendek' => 'POS Bandara Husen',
            'tipe' => 'Pos Bandara',
            'moda' => 'Udara',
            'zona' => 'Kota Bandung',
            'alamat' => 'Jl. Pajajaran Dalam, Husen Sastranegara, Kec. Cicendo, Kota Bandung, Jawa Barat 40174',
            'latitude' => -6.9006,
            'longitude' => 107.5763,
            'is_induk' => false,
            'is_active' => true,
        ],
        [
            'no' => 3,
            'kode' => 'WLK-CBN-03',
            'nama' => 'Wilayah Kerja Cirebon',
            'nama_pendek' => 'Wilker Cirebon',
            'tipe' => 'Wilayah Kerja',
            'moda' => 'Laut & Pesisir',
            'zona' => 'Kota Cirebon',
            'alamat' => 'Jl. Terusan Jl. Sekar Kemuning II No. 7, Karyamulya, Kec. Kesambi, Kota Cirebon, Jawa Barat 45131',
            'latitude' => -6.7320,
            'longitude' => 108.5523,
            'is_induk' => false,
            'is_active' => true,
        ],
        [
            'no' => 4,
            'kode' => 'WLK-IDM-04',
            'nama' => 'Wilayah Kerja Indramayu',
            'nama_pendek' => 'Wilker Indramayu',
            'tipe' => 'Wilayah Kerja',
            'moda' => 'Laut & Pesisir',
            'zona' => 'Kab. Indramayu',
            'alamat' => 'Jl. Jend. Sudirman, Lemahmekar, Kec. Indramayu, Kabupaten Indramayu, Jawa Barat 45213',
            'latitude' => -6.3276,
            'longitude' => 108.3249,
            'is_induk' => false,
            'is_active' => true,
        ],
        [
            'no' => 5,
            'kode' => 'WLK-KJT-05',
            'nama' => 'Wilayah Kerja Kertajati',
            'nama_pendek' => 'Wilker Kertajati',
            'tipe' => 'Wilayah Kerja',
            'moda' => 'Udara Internasional',
            'zona' => 'Kab. Majalengka',
            'alamat' => 'Kertajati, Majalengka Regency, West Java 45457',
            'latitude' => -6.6561,
            'longitude' => 108.1578,
            'is_induk' => false,
            'is_active' => true,
        ],
        [
            'no' => 6,
            'kode' => 'WLK-PTB-06',
            'nama' => 'Wilayah Kerja Patimban',
            'nama_pendek' => 'Wilker Patimban',
            'tipe' => 'Wilayah Kerja',
            'moda' => 'Pelabuhan Laut',
            'zona' => 'Kab. Subang',
            'alamat' => 'Pelabuhan Patimban, Pusakanagara, Subang, Jawa Barat 41255',
            'latitude' => -6.2415,
            'longitude' => 107.9048,
            'is_induk' => false,
            'is_active' => true,
        ],
        [
            'no' => 7,
            'kode' => 'WLK-PLR-07',
            'nama' => 'Wilayah Kerja Pelabuharatu',
            'nama_pendek' => 'Wilker Pelabuharatu',
            'tipe' => 'Wilayah Kerja',
            'moda' => 'Pelabuhan Samudera',
            'zona' => 'Kab. Sukabumi',
            'alamat' => 'Jl. Sirgalih, Pelabuhanratu, Kec. Pelabuhanratu, Kabupaten Sukabumi, Jawa Barat 43364',
            'latitude' => -6.9875,
            'longitude' => 106.5514,
            'is_induk' => false,
            'is_active' => true,
        ],
    ];

    /**
     * Get active offices sorted by number.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('no');
    }

    /**
     * Offices for Maritime / Pelabuhan / Laut (Patimban, Cirebon, Indramayu, Pelabuharatu, Induk).
     */
    public function scopeLaut($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->where('is_induk', true)
                  ->orWhere('kode', 'like', '%CBN%')
                  ->orWhere('kode', 'like', '%IDM%')
                  ->orWhere('kode', 'like', '%PTB%')
                  ->orWhere('kode', 'like', '%PLR%')
                  ->orWhere('moda', 'like', '%Laut%')
                  ->orWhere('moda', 'like', '%Pelabuhan%');
            })
            ->orderByRaw('is_induk ASC, `no` ASC');
    }

    /**
     * Offices for Aviation / Bandara / Udara (Husen Sastranegara, Kertajati BIJB, Induk).
     */
    public function scopeUdara($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->where('is_induk', true)
                  ->orWhere('kode', 'like', '%BDO%')
                  ->orWhere('kode', 'like', '%KJT%')
                  ->orWhere('moda', 'like', '%Udara%')
                  ->orWhere('tipe', 'like', '%Bandara%');
            })
            ->orderByRaw('is_induk ASC, `no` ASC');
    }

    /**
     * Scope to find by name or short name.
     */
    public function scopeFindByName($query, string $name)
    {
        return $query->where('nama', $name)
            ->orWhere('nama_pendek', $name)
            ->orWhere('kode', $name);
    }
}

