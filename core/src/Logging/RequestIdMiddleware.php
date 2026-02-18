<?php

declare(strict_types=1);

namespace Constelation\Shared\Core\Logging;

use Closure;
use Illuminate\Http\Request;
use Ramsey\Uuid\Uuid;

/**
 * Middleware to ensure every request has a unique Request ID
 *
 * This middleware should be applied to all services to enable
 * distributed tracing across the microservices architecture.
 *
 * Usage in bootstrap/app.php:
 *   $app->middleware([
 *       \Constelation\Shared\Core\Logging\RequestIdMiddleware::class,
 *   ]);
 */
final class RequestIdMiddleware
{
    public const HEADER_NAME = 'X-Request-ID';
    public const CORRELATION_ID_HEADER = 'X-Correlation-ID';

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Get or generate Request ID
        $requestId = $request->header(self::HEADER_NAME);
        if (empty($requestId)) {
            $requestId = $this->generateRequestId();
        }

        // Get or inherit Correlation ID (for tracking across service calls)
        $correlationId = $request->header(self::CORRELATION_ID_HEADER);
        if (empty($correlationId)) {
            $correlationId = $requestId; // First service in chain sets correlation ID
        }

        // Set headers on the request
        $request->headers->set(self::HEADER_NAME, $requestId);
        $request->headers->set(self::CORRELATION_ID_HEADER, $correlationId);

        // Store in request attributes for easy access
        $request->attributes->set('request_id', $requestId);
        $request->attributes->set('correlation_id', $correlationId);

        // Process request
        $response = $next($request);

        // Add IDs to response headers
        $response->headers->set(self::HEADER_NAME, $requestId);
        $response->headers->set(self::CORRELATION_ID_HEADER, $correlationId);

        return $response;
    }

    /**
     * Generate a unique request ID
     */
    private function generateRequestId(): string
    {
        return Uuid::uuid4()->toString();
    }
}
