<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class PrlSiveraVektor extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'prl_sivera_vektor';

    protected $fillable = [
        'source_id',
        'jenis_vektor',
        'wilker',
        'lokasi',
        'tanggal',
        'parameter_utama',
        'nilai_indeks',
        'baku_mutu_status',
        'detail_data',
        'tindakan_pengendalian',
        'created_by_name',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nilai_indeks' => 'float',
        'detail_data' => 'array',
    ];

    /**
     * Human-readable clean display for tindakan pengendalian.
     * Prevents raw JSON from leaking into the UI.
     */
    public function getTindakanPengendalianAttribute($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        if (is_string($value) && str_starts_with(trim($value), '{')) {
            $data = json_decode($value, true);
            if (is_array($data)) {
                $parts = [];
                if (!empty($data['jentik_kimia']) || !empty($data['jentik_kimia_gram']) || !empty($data['jentik_kimia_sachet'])) {
                    $p = [];
                    if (!empty($data['jentik_kimia_gram'])) $p[] = $data['jentik_kimia_gram'] . ' gr';
                    if (!empty($data['jentik_kimia_sachet'])) $p[] = $data['jentik_kimia_sachet'] . ' sachet';
                    $parts[] = 'Larvasidasi' . (!empty($p) ? ' (' . implode(', ', $p) . ')' : '');
                }
                if (!empty($data['jentik_fisik'])) {
                    $parts[] = 'Fisik (3M Plus/Kuras)';
                }
                if (!empty($data['jentik_biologi'])) {
                    $parts[] = 'Biologi (BTI/Ikan)';
                }
                if (!empty($data['nyamuk_fogging']) || !empty($data['nyamuk_fogging_ml'])) {
                    $f = [];
                    if (!empty($data['nyamuk_fogging_ml'])) $f[] = $data['nyamuk_fogging_ml'] . ' ml';
                    if (!empty($data['nyamuk_fogging_merk'])) $f[] = $data['nyamuk_fogging_merk'];
                    $parts[] = 'Fogging' . (!empty($f) ? ' (' . implode(' ', $f) . ')' : '');
                }
                if (!empty($data['nyamuk_fisik'])) {
                    $parts[] = 'Fisik Nyamuk (Trap/Raket)';
                }
                if (!empty($data['spraying']) || !empty($data['spraying_ml'])) {
                    $parts[] = 'Spraying' . (!empty($data['spraying_ml']) ? ' (' . $data['spraying_ml'] . ' ml)' : '');
                }
                if (!empty($data['granule']) || !empty($data['granule_gram'])) {
                    $parts[] = 'Granule Bait' . (!empty($data['granule_gram']) ? ' (' . $data['granule_gram'] . ' gr)' : '');
                }
                if (!empty($data['retrapping']) || !empty($data['retrapping_trap'])) {
                    $parts[] = 'Retrapping' . (!empty($data['retrapping_trap']) ? ' (' . $data['retrapping_trap'] . ' trap)' : '');
                }
                if (!empty($data['gel_bait']) || !empty($data['gel_bait_gram'])) {
                    $parts[] = 'Gel Bait' . (!empty($data['gel_bait_gram']) ? ' (' . $data['gel_bait_gram'] . ' gr)' : '');
                }
                if (!empty($data['catatan'])) {
                    $parts[] = $data['catatan'];
                }
                if (!empty($parts)) {
                    return implode('; ', $parts);
                }
            }
        }

        return $value;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['jenis_vektor', 'wilker', 'lokasi', 'nilai_indeks', 'baku_mutu_status'])
            ->logOnlyDirty()
            ->useLogName('prl_sivera_vektor');
    }
}

