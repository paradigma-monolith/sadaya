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
        return Inertia::render('DatabaseImport/Index', [
            'modules' => array_values($service->getModuleConfigs()),
            'sadaya_stats' => $service->getSadayaStats(),
            'default_config' => [
                'host' => '127.0.0.1',
                'port' => 3306,
                'username' => 'root',
                'password' => 'mysql',
            ],
            'selected_module' => $request->query('module', 'all'),
        ]);
    }

    /**
     * Test connection to MySQL and probe all modules.
     */
    public function testConnection(Request $request, SimplyDataMigrationService $service): JsonResponse
    {
        $credentials = $request->only(['host', 'port', 'username', 'password']);
        $result = $service->testAll($credentials);

        return response()->json($result);
    }

    /**
     * Execute migration for a specific module or all modules.
     */
    public function migrate(Request $request, SimplyDataMigrationService $service): JsonResponse
    {
        $module = $request->input('module', 'all');
        $credentials = $request->only(['host', 'port', 'username', 'password', 'database', 'table']);
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

