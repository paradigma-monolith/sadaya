<?php

namespace Database\Seeders;

use App\Models\KantorWilker;
use Illuminate\Database\Seeder;

class KantorWilkerSeeder extends Seeder
{
    public function run(): void
    {
        foreach (KantorWilker::DEFAULT_OFFICES as $office) {
            KantorWilker::updateOrCreate(
                ['kode' => $office['kode']],
                $office
            );
        }
    }
}
