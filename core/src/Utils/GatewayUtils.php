<?php

declare(strict_types=1);

namespace Constelation\Shared\Utils;

final class GatewayUtils
{
    public static function getGatewayUrl(string $service): string
    {
        return env('GATEWAY_URL', 'http://localhost:8000') . "/{$service}";
    }

    public static function getGatewayUrlWithPath(string $service, string $path): string
    {
		$gatewayUrl = self::getGatewayUrl($service);
        return "{$gatewayUrl}/{$path}";
    }

	public static function getGatewayUrlFromRoute(string $service, string $route): string
	{
		$gatewayUrl = self::getGatewayUrl($service);
		$parsedRoute = parse_url($route);
		$path = ltrim($parsedRoute['path'], '/');
		$query = $parsedRoute['query'] ?? '';
		return "{$gatewayUrl}/{$path}" . ($query ? "?{$query}" : '');
	}
}