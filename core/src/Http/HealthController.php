<?php
declare(strict_types=1);

namespace Constelation\Shared\Core\Http;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller as BaseController;
use Throwable;

class HealthController extends BaseController
{
    public function health(): JsonResponse
    {
        return response()->json([
            'status' => 'healthy',
            'service' => config('app.name', 'unknown-service'),
            'version' => config('app.version', '1.0.0'),
            'uptime' => $this->getUptime(),
            'checks' => [
                'database' => $this->checkDatabase(),
                'redis' => $this->checkRedis(),
                'queue' => $this->checkQueue(),
            ],
        ]);
    }

    public function ready(): JsonResponse
    {
        $databaseOk = $this->checkDatabase() === 'up';
        $migrationsOk = $this->checkMigrations();
        $isReady = $databaseOk && $migrationsOk;

        return response()->json([
            'status' => $isReady ? 'ready' : 'not_ready',
            'service' => config('app.name', 'unknown-service'),
            'checks' => [
                'database' => $databaseOk ? 'up' : 'down',
                'migrations' => $migrationsOk ? 'up' : 'down',
            ],
        ], $isReady ? 200 : 503);
    }

    protected function checkDatabase(): string
    {
        try {
            DB::connection()->getPdo();
            return 'up';
        } catch (Throwable) {
            return 'down';
        }
    }

    protected function checkRedis(): string
    {
        try {
            Cache::put('__health_check__', true, 5);
            return Cache::get('__health_check__') === true ? 'up' : 'down';
        } catch (Throwable) {
            return 'down';
        }
    }

    protected function checkQueue(): string
    {
        return config('queue.default') !== null ? 'up' : 'not_configured';
    }

    protected function checkMigrations(): bool
    {
        try {
            DB::table('migrations')->limit(1)->exists();
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    protected function getUptime(): int
    {
        if (!isset($_SERVER['REQUEST_TIME_FLOAT'])) {
            return 0;
        }

        return max(0, (int) (microtime(true) - (float) $_SERVER['REQUEST_TIME_FLOAT']));
    }
}
