<?php

namespace App\Jobs;

use App\Exports\AuditLogsExport;
use App\Models\User;
use App\Notifications\SystemAlertNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;

class GenerateAuditLogExportJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 2;

    public int $timeout = 180;

    public readonly string $format;

    public function __construct(
        public readonly int $requestedByUserId,
        string $format = 'xlsx',
    ) {
        $this->format = strtolower($format) === 'csv' ? 'csv' : 'xlsx';
    }

    /**
     * Generate the requested audit log spreadsheet on private local storage and notify the user.
     */
    public function handle(): void
    {
        $user = User::find($this->requestedByUserId);
        if ($user === null) {
            return;
        }

        $filename = "audit-logs-{$this->requestedByUserId}-".now()->format('Ymd-His').".{$this->format}";
        $storagePath = "exports/{$filename}";
        $writerType = $this->format === 'csv' ? ExcelFormat::CSV : ExcelFormat::XLSX;

        Excel::store(new AuditLogsExport, $storagePath, 'local', $writerType);

        $user->notify(new SystemAlertNotification(
            'Ekspor Log Audit Siap',
            "Berkas {$filename} telah berhasil diproses di penyimpanan privat.",
            'success',
            '/audit-logs'
        ));
    }
}
