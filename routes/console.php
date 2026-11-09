<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Automatic Background Synchronization for Simply (CI4) Database
Schedule::command('simply:auto-sync')->everyMinute()->withoutOverlapping();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('docs:generate-pdf', function () {
    $this->info('Generating Manual Book PDF...');
    
    // Ensure public/docs directory exists
    if (!file_exists(public_path('docs'))) {
        mkdir(public_path('docs'), 0755, true);
    }

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('docs.manual-book-pdf', [
        'generatedAt' => now(),
    ])
    ->setPaper('a4', 'portrait')
    ->setOption(['isRemoteEnabled' => true, 'isHtml5ParserEnabled' => true, 'isPhpEnabled' => true]);

    $publicPath = public_path('docs/MANUAL_BOOK_STARTERKIT_PARADIGMA.pdf');
    $rootPath = base_path('MANUAL_BOOK_STARTERKIT_PARADIGMA.pdf');

    $pdf->save($publicPath);
    $pdf->save($rootPath);

    $this->info("Manual Book PDF generated successfully!");
    $this->line("- Root: {$rootPath}");
    $this->line("- Public: {$publicPath}");
})->purpose('Generate comprehensive Manual Book PDF for Starter Kit');
