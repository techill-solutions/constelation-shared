<?php

declare(strict_types=1);

namespace Constelation\Shared\Auth;

use Constelation\Shared\Auth\DTOs\CompanyDTO;
use Constelation\Shared\Auth\DTOs\UserDTO;

/**
 * Authentication Context
 * 
 * Aggregates user and company data for the current request.
 * This is the main object that should be injected into requests
 * by the authentication middleware.
 */
final class AuthContext
{
    public function __construct(
        public readonly UserDTO $user,
        public readonly CompanyDTO $company,
        public readonly ?string $token = null,
        public readonly ?int $tokenExpiresAt = null,
    ) {}

    /**
     * Create from JWT payload.
     */
    public static function fromJwtPayload(array $payload, ?string $token = null): self
    {
        $user = UserDTO::fromArray($payload['user'] ?? $payload);
        
        $companyData = $payload['company'] ?? [];
        if (empty($companyData) && isset($payload['company_id'])) {
            $companyData = ['id' => $payload['company_id']];
        }
        $company = CompanyDTO::fromArray($companyData);

        return new self(
            user: $user,
            company: $company,
            token: $token,
            tokenExpiresAt: $payload['exp'] ?? null,
        );
    }

    /**
     * Create from request attributes (for testing or internal calls).
     */
    public static function fromAttributes(array $attributes): self
    {
        return new self(
            user: UserDTO::fromArray([
                'id' => $attributes['user_id'] ?? 0,
                'company_id' => $attributes['company_id'] ?? 0,
                'name' => $attributes['user_name'] ?? 'Test User',
                'email' => $attributes['user_email'] ?? 'test@test.com',
            ]),
            company: CompanyDTO::fromArray([
                'id' => $attributes['company_id'] ?? 0,
                'name' => $attributes['company_name'] ?? 'Test Company',
                'document' => $attributes['company_document'] ?? '00.000.000/0001-00',
            ]),
        );
    }

    /**
     * Convert to array for serialization.
     */
    public function toArray(): array
    {
        return [
            'user' => $this->user->toArray(),
            'company' => $this->company->toArray(),
            'token_expires_at' => $this->tokenExpiresAt,
        ];
    }

    /**
     * Get the company ID for multi-tenancy queries.
     */
    public function getCompanyId(): int
    {
        return $this->company->id;
    }

    /**
     * Get the user ID.
     */
    public function getUserId(): int
    {
        return $this->user->id;
    }

    /**
     * Check if the token is expired.
     */
    public function isTokenExpired(): bool
    {
        if ($this->tokenExpiresAt === null) {
            return false;
        }

        return time() >= $this->tokenExpiresAt;
    }

    /**
     * Check if user has permission.
     */
    public function hasPermission(string $permission): bool
    {
        return $this->user->hasPermission($permission);
    }

    /**
     * Check if user has role.
     */
    public function hasRole(string $role): bool
    {
        return $this->user->hasRole($role);
    }

    /**
     * Check if user is active and company is active.
     */
    public function isFullyActive(): bool
    {
        return $this->user->isActive && $this->company->isActive;
    }
}
