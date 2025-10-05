<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class PayloadController extends Controller
{
    /**
     * Store a payload in cache
     *
     * POST /api/payload
     * Body: { "type": "payload1" } or { "type": "payload2" }
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:payload1,payload2'
            ]);

            $type = $validated['type'];

            // Load example payload from storage
            $filename = $type . '.json';

            if (!Storage::exists($filename)) {
                return response()->json([
                    'error' => "Example payload file not found: {$filename}"
                ], 404);
            }

            $payload = json_decode(Storage::get($filename), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return response()->json([
                    'error' => 'Invalid JSON in payload file'
                ], 500);
            }

            // Store in cache for 1 hour
            Cache::put($type, $payload, now()->addHour());

            Log::info("Stored {$type} in cache");

            return response()->json([
                'ok' => true,
                'received' => $type
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }
}
