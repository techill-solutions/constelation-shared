<?php

declare(strict_types=1);

namespace Constelation\Shared\Core\Logging;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

/**
 * Structured Logger for consistent logging across all services
 *
 * Usage:
 *   $logger = new StructuredLogger('real-estate');
 *   $logger->info('Property created', ['property_id' => 123]);
 */
final class StructuredLogger
{
    private string $service;
    private ?string $requestId;
    private ?int $userId;
    private ?int $companyId;
    private array $defaultContext;

    public function __construct(
        string $service,
        ?string $requestId = null,
        ?int $userId = null,
        ?int $companyId = null,
        array $defaultContext = []
    ) {
        $this->service = $service;
        $this->requestId = $requestId;
        $this->userId = $userId;
        $this->companyId = $companyId;
        $this->defaultContext = $defaultContext;
    }

    /**
     * Create logger from request
     */
    public static function fromRequest(Request $request, string $service): self
    {
        return new self(
            service: $service,
            requestId: $request->header('X-Request-ID'),
            userId: $request->attributes->get('user_id'),
            companyId: $request->attributes->get('company_id'),
        );
    }

    /**
     * Log an emergency message
     */
    public function emergency(string $message, array $context = []): void
    {
        $this->log('emergency', $message, $context);
    }

    /**
     * Log an alert message
     */
    public function alert(string $message, array $context = []): void
    {
        $this->log('alert', $message, $context);
    }

    /**
     * Log a critical message
     */
    public function critical(string $message, array $context = []): void
    {
        $this->log('critical', $message, $context);
    }

    /**
     * Log an error message
     */
    public function error(string $message, array $context = []): void
    {
        $this->log('error', $message, $context);
    }

    /**
     * Log a warning message
     */
    public function warning(string $message, array $context = []): void
    {
        $this->log('warning', $message, $context);
    }

    /**
     * Log a notice message
     */
    public function notice(string $message, array $context = []): void
    {
        $this->log('notice', $message, $context);
    }

    /**
     * Log an info message
     */
    public function info(string $message, array $context = []): void
    {
        $this->log('info', $message, $context);
    }

    /**
     * Log a debug message
     */
    public function debug(string $message, array $context = []): void
    {
        $this->log('debug', $message, $context);
    }

    /**
     * Log an exception
     */
    public function exception(\Throwable $exception, string $message = 'Exception occurred', array $context = []): void
    {
        $this->error($message, array_merge($context, [
            'exception_class' => get_class($exception),
            'exception_message' => $exception->getMessage(),
            'exception_code' => $exception->getCode(),
            'exception_file' => $exception->getFile(),
            'exception_line' => $exception->getLine(),
            'exception_trace' => $this->formatTrace($exception->getTrace()),
        ]));
    }

    /**
     * Log a performance metric
     */
    public function performance(string $operation, float $durationMs, array $context = []): void
    {
        $this->info('Performance metric', array_merge($context, [
            'operation' => $operation,
            'duration_ms' => round($durationMs, 2),
        ]));
    }

    /**
     * Log a security event
     */
    public function security(string $event, array $context = []): void
    {
        $this->warning('Security event', array_merge($context, [
            'security_event' => $event,
        ]));
    }

    /**
     * Log a business event
     */
    public function businessEvent(string $event, array $context = []): void
    {
        $this->info('Business event', array_merge($context, [
            'business_event' => $event,
        ]));
    }

    /**
     * Start a timed operation
     */
    public function startTimer(): float
    {
        return microtime(true);
    }

    /**
     * End a timed operation and log it
     */
    public function endTimer(float $startTime, string $operation, array $context = []): float
    {
        $durationMs = (microtime(true) - $startTime) * 1000;
        $this->performance($operation, $durationMs, $context);
        return $durationMs;
    }

    /**
     * Create a child logger with additional context
     */
    public function withContext(array $context): self
    {
        return new self(
            service: $this->service,
            requestId: $this->requestId,
            userId: $this->userId,
            companyId: $this->companyId,
            defaultContext: array_merge($this->defaultContext, $context),
        );
    }

    /**
     * Internal log method
     */
    private function log(string $level, string $message, array $context): void
    {
        $structuredContext = $this->buildContext($context);
        Log::{$level}($message, $structuredContext);
    }

    /**
     * Build the full context for logging
     */
    private function buildContext(array $context): array
    {
        return array_merge(
            [
                '@timestamp' => now()->toIso8601String(),
                '@service' => $this->service,
                '@version' => '1.0',
                'request_id' => $this->requestId,
                'user_id' => $this->userId,
                'company_id' => $this->companyId,
                'environment' => app()->environment(),
                'hostname' => gethostname(),
            ],
            $this->defaultContext,
            $context
        );
    }

    /**
     * Format stack trace for logging
     */
    private function formatTrace(array $trace): array
    {
        return array_slice(array_map(function ($frame) {
            return sprintf(
                '%s:%s %s%s%s()',
                $frame['file'] ?? 'unknown',
                $frame['line'] ?? 0,
                $frame['class'] ?? '',
                $frame['type'] ?? '',
                $frame['function'] ?? 'unknown'
            );
        }, $trace), 0, 10); // Limit to 10 frames
    }
}
