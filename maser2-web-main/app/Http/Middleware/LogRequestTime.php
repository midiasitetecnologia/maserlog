<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class LogRequestTime
{
    public function handle($request, Closure $next)
    {
        $start = microtime(true);

        $queryTime = 0;
        $queryCount = 0;

        // Captura tempo de queries
        DB::listen(function ($query) use (&$queryTime, &$queryCount) {
            $queryTime += $query->time; // ms
            $queryCount++;
        });

        $response = $next($request);

        $totalTime = microtime(true) - $start; // segundos
        $threshold = env('SLOW_REQUEST_THRESHOLD', 5);

        if ($totalTime > $threshold) {

            Log::warning('SLOW_REQUEST', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),

                // tempo total
                'total_time_s' => round($totalTime, 3),

                // banco
                'db_time_ms' => round($queryTime, 2),
                'db_time_s' => round($queryTime / 1000, 3),
                'query_count' => $queryCount,

                // memória
                'memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 2),
            ]);
        }

        return $response;
    }
}