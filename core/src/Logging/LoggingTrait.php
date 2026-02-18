<?php

declare(strict_types=1);

namespace Constelation\Shared\Core\Logging;

use Illuminate\Http\Request;

/**
 * Trait for adding structured logging capabilities to controllers and services
 *
 * Usage in a controller:
 *   use LoggingTrait;
 *
 *   public function store(Request $request)
 *   {
 *       $this->logInfo('Creating property', ['data' => $request->all()]);
 *       // ...
 *       $this->logInfo('Property created', ['property_id' => $property->id]);
 *   }
 */
trait LoggingTrait
{
    protected ?StructuredLogger $logger = null;

    /**
     * Get or create the logger instance
     */
    protected function getLogger(): StructuredLogger
    {
        if ($this->logger === null) {
            $this->logger = new StructuredLogger($this->getServiceName());
        }
        return $this->logger;
    }

    /**
     * Initialize logger from request
     */
    protected function initLoggerFromRequest(Request $request): StructuredLogger
    {
        $this->logger = StructuredLogger::fromRequest($request, $this->getServiceName());
        return $this->logger;
    }

    /**
     * Get the service name for logging
     * Override this method in your class to customize
     */
    protected function getServiceName(): string
    {
        // Default: extract from class namespace
        $class = get_class($this);
        $parts = explode('\\', $class);

        // Try to find service name from namespace
        foreach ($parts as $index => $part) {
            if (in_array(strtolower($part), ['realestate', 'real-estate', 'auth', 'payments', 'gateway', 'files', 'notifications'])) {
                return strtolower($part);
            }
        }

        // Fallback to class name
        return strtolower(end($parts));
    }

    /**
     * Log an info message
     */
    protected function logInfo(string $message, array $context = []): void
    {
        $this->getLogger()->info($message, $context);
    }

    /**
     * Log a debug message
     */
    protected function logDebug(string $message, array $context = []): void
    {
        $this->getLogger()->debug($message, $context);
    }

    /**
     * Log a warning message
     */
    protected function logWarning(string $message, array $context = []): void
    {
        $this->getLogger()->warning($message, $context);
    }

    /**
     * Log an error message
     */
    protected function logError(string $message, array $context = []): void
    {
        $this->getLogger()->error($message, $context);
    }

    /**
     * Log an exception
     */
    protected function logException(\Throwable $e, string $message = 'Exception occurred', array $context = []): void
    {
        $this->getLogger()->exception($e, $message, $context);
    }

    /**
     * Log a business event
     */
    protected function logBusinessEvent(string $event, array $context = []): void
    {
        $this->getLogger()->businessEvent($event, $context);
    }

    /**
     * Log a security event
     */
    protected function logSecurityEvent(string $event, array $context = []): void
    {
        $this->getLogger()->security($event, $context);
    }

    /**
     * Start a performance timer
     */
    protected function startTimer(): float
    {
        return $this->getLogger()->startTimer();
    }

    /**
     * End timer and log performance
     */
    protected function endTimer(float $startTime, string $operation, array $context = []): float
    {
        return $this->getLogger()->endTimer($startTime, $operation, $context);
    }
}
