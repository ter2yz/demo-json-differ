<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\DiffService;

class CompareController extends Controller
{
    protected $diffService;

    public function __construct(DiffService $diffService)
    {
        $this->diffService = $diffService;
    }

    /**
     * Compare two stored payloads
     *
     * GET /api/compare
     */
    public function compare()
    {
        $payload1 = Cache::get('payload1');
        $payload2 = Cache::get('payload2');

        if (!$payload1 || !$payload2) {
            return response()->json([
                'error' => 'Missing payloads. Please send both payload1 and payload2 first.'
            ], 400);
        }

        $diffs = $this->diffService->compareJsonAsLines($payload1, $payload2);

        return response()->json([
            'diffs' => $diffs
        ]);
    }

    /**
     * Compare custom payloads sent directly
     *
     * POST /api/compare-custom
     * Body: { "payload1": {...}, "payload2": {...} }
     */
    public function compareCustom(Request $request)
    {
        try {
            $validated = $request->validate([
                'payload1' => 'required',
                'payload2' => 'required'
            ]);

            $diffs = $this->diffService->compareJsonAsLines(
                $validated['payload1'],
                $validated['payload2']
            );

            return response()->json([
                'diffs' => $diffs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Comparison failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
