<?php

namespace App\Http\Controllers;

use App\Services\SimplyDataMigrationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DataMigrationController extends Controller
{
    /**
     * Display the Central Database Migration & Import Center.
     */
    public function index(Request $request, SimplyDataMigrationService $service): Response
    {
        $rawConfigs = $service->getModuleConfigs();
        $defaultModuleConfigs = [];
        foreach ($rawConfigs as $modKey => $cfg) {
            $defaultModuleConfigs[$modKey] = [
                'host' => '127.0.0.1',
                'port' => 3306,
                'username' => 'root',
                'password' => 'mysql',
                'database' => $cfg['source_db'],
                'table' => $cfg['source_table'],
            ];
        }

        // Merge saved persistent configs from storage if available
        $savedPath = storage_path('app/simply_migration_configs.json');
        if (file_exists($savedPath)) {
            $saved = json_decode(file_get_contents($savedPath), true);
            if (is_array($saved)) {
                foreach ($saved as $k => $vals) {
                    if (isset($defaultModuleConfigs[$k]) && is_array($vals)) {
                        $defaultModuleConfigs[$k] = array_merge($defaultModuleConfigs[$k], $vals);
                    }
                }
            }
        }

        // Load automatic synchronization configuration if available
        $autoSyncPath = storage_path('app/simply_sync_schedule.json');
        $autoSyncConfig = [
            'enabled' => false,
            'frequency' => 'hourly',
            'modules' => array_keys($rawConfigs),
            'last_run_at' => null,
            'last_status' => null,
            'last_imported_count' => 0,
            'last_duration' => 0,
            'last_message' => null,
        ];
        if (file_exists($autoSyncPath)) {
            $savedAutoSync = json_decode(file_get_contents($autoSyncPath), true);
            if (is_array($savedAutoSync)) {
                $autoSyncConfig = array_merge($autoSyncConfig, $savedAutoSync);
            }
        }

        return Inertia::render('DatabaseImport/Index', [
            'modules' => array_values($rawConfigs),
            'sadaya_stats' => $service->getSadayaStats(),
            'default_config' => [
                'host' => '127.0.0.1',
                'port' => 3306,
                'username' => 'root',
                'password' => 'mysql',
            ],
            'default_module_configs' => $defaultModuleConfigs,
            'auto_sync_config' => $autoSyncConfig,
            'selected_module' => $request->query('module', 'all'),
        ]);
    }

    /**
     * Save customized module database credentials to persistent storage.
     */
    public function saveConfig(Request $request): JsonResponse
    {
        $modules = $request->input('modules', []);
        if (!is_array($modules)) {
            return response()->json(['success' => false, 'message' => 'Data konfigurasi tidak valid.'], 422);
        }

        $savedPath = storage_path('app/simply_migration_configs.json');
        file_put_contents($savedPath, json_encode($modules, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return response()->json([
            'success' => true,
            'message' => 'Konfigurasi koneksi MySQL per modul berhasil disimpan secara permanen.',
        ]);
    }

    /**
     * Save auto-sync schedule configuration.
     */
    public function saveAutoSync(Request $request): JsonResponse
    {
        $enabled = (bool)$request->input('enabled', false);
        $frequency = (string)$request->input('frequency', 'hourly');
        $modules = $request->input('modules', []);

        $autoSyncPath = storage_path('app/simply_sync_schedule.json');
        $current = [
            'enabled' => false,
            'frequency' => 'hourly',
            'modules' => [],
            'last_run_at' => null,
            'last_status' => null,
            'last_imported_count' => 0,
            'last_duration' => 0,
            'last_message' => null,
        ];

        if (file_exists($autoSyncPath)) {
            $saved = json_decode(file_get_contents($autoSyncPath), true);
            if (is_array($saved)) {
                $current = array_merge($current, $saved);
            }
        }

        $current['enabled'] = $enabled;
        $current['frequency'] = $frequency;
        $current['modules'] = is_array($modules) ? $modules : [];

        file_put_contents($autoSyncPath, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan sinkronisasi otomatis berhasil disimpan.',
            'auto_sync_config' => $current,
        ]);
    }

    /**
     * Force run auto-sync immediately from web UI.
     */
    public function runAutoSyncNow(Request $request, SimplyDataMigrationService $service): JsonResponse
    {
        \Illuminate\Support\Facades\Artisan::call('simply:auto-sync', ['--force' => true]);
        $output = \Illuminate\Support\Facades\Artisan::output();

        $autoSyncPath = storage_path('app/simply_sync_schedule.json');
        $current = [];
        if (file_exists($autoSyncPath)) {
            $current = json_decode(file_get_contents($autoSyncPath), true) ?: [];
        }

        return response()->json([
            'success' => true,
            'message' => 'Sinkronisasi otomatis berhasil dieksekusi.',
            'output' => $output,
            'auto_sync_config' => $current,
            'sadaya_current' => $service->getSadayaStats(),
        ]);
    }

    /**
     * Test connection to MySQL and probe all modules or a specific module.
     */
    public function testConnection(Request $request, SimplyDataMigrationService $service): JsonResponse
    {
        $payload = $request->all();
        $targetModule = $request->input('target_module');

        if ($targetModule && $targetModule !== 'all') {
            $res = $service->testModule($targetModule, $payload);
            return response()->json([
                'success' => ($res['connected'] && $res['table_exists']),
                'message' => ($res['connected'] && $res['table_exists'])
                    ? "Koneksi ke modul {$res['name']} berhasil ({$res['count']} baris)."
                    : ($res['error'] ?? 'Gagal terhubung'),
                'modules' => [$targetModule => $res],
                'sadaya_current' => $service->getSadayaStats(),
            ]);
        }

        $result = $service->testAll($payload);

        return response()->json($result);
    }

    /**
     * Execute migration for a specific module or all modules.
     */
    public function migrate(Request $request, SimplyDataMigrationService $service): JsonResponse
    {
        $module = $request->input('module', 'all');
        $credentials = $request->all();
        $options = [
            'skip_duplicate' => (bool)$request->input('skip_duplicate', true),
            'truncate_first' => (bool)$request->input('truncate_first', false),
            'recalculate_pnbp' => (bool)$request->input('recalculate_pnbp', true),
            'normalize_ports' => (bool)$request->input('normalize_ports', true),
            'dry_run' => (bool)$request->input('dry_run', false),
        ];

        if ($module === 'all') {
            $result = $service->migrateAll($credentials, $options);
        } else {
            $result = $service->migrateModule($module, $credentials, $options);
            $result['sadaya_current'] = $service->getSadayaStats();
        }

        return response()->json($result);
    }

    /**
     * Import a specific module from uploaded file (.sql, .json, .csv).
     */
    public function importFile(Request $request, SimplyDataMigrationService $service): JsonResponse
    {
        $request->validate([
            'module' => 'required|string',
            'file' => 'required|file|max:51200',
        ]);

        $module = $request->input('module');
        $file = $request->file('file');
        $options = [
            'skip_duplicate' => (bool)$request->input('skip_duplicate', true),
            'truncate_first' => (bool)$request->input('truncate_first', false),
        ];

        $result = $service->importFromFile($module, $file, $options);
        $result['sadaya_current'] = $service->getSadayaStats();

        return response()->json($result);
    }
}

