<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;
use Spatie\Activitylog\Models\Activity;

class ActivityLogApiController extends Controller
{
    /**
     * Return paginated audit trail logs for API clients.
     */
    public function index(): JsonResponse
    {
        Gate::authorize('viewAny', Activity::class);

        $activities = Activity::with('causer')
            ->latest()
            ->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $activities,
        ]);
    }
}
