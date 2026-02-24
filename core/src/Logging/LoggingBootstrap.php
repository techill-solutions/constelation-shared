<?php
declare(strict_types=1);

namespace Constelation\Shared\Core\Logging;

use Laravel\Lumen\Application;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\FormattableHandlerInterface;
use Monolog\Logger;
use Throwable;

final class LoggingBootstrap
{
    public static function configure(Application $app): void
    {
        if (!method_exists($app, 'configureMonologUsing')) {
            return;
        }

        $app->{'configureMonologUsing'}(static function (Logger $monolog): Logger {
            $monolog->pushProcessor(static function (array $record): array {
                $requestId = null;
                $correlationId = null;

                try {
                    $request = request();
                    $requestId = $request?->headers->get('X-Request-ID');
                    $correlationId = $request?->headers->get('X-Correlation-ID');
                } catch (Throwable) {
                    // Request context is not always available (e.g., CLI commands).
                }

                $record['extra']['request_id'] = $requestId;
                $record['extra']['correlation_id'] = $correlationId;

                return $record;
            });

            foreach ($monolog->getHandlers() as $handler) {
                if ($handler instanceof FormattableHandlerInterface) {
                    $handler->setFormatter(new JsonFormatter());
                }
            }

            return $monolog;
        });
    }
}
