<?php

declare(strict_types=1);

namespace Constelation\Shared\Auth\DTOs;

/**
 * Data Transfer Object for authenticated user data.
 * 
 * This DTO represents user data that transits between services via JWT/session.
 * It does not depend on any database table - data comes from the Auth service.
 */
final class UserDTO
{
    public function __construct(
        public readonly int $id,
        public readonly int $companyId,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $phone = null,
        public readonly ?string $avatar = null,
        public readonly bool $isActive = true,
        public readonly array $roles = [],
        public readonly array $permissions = [],
    ) {}

    /**
     * Create from JWT payload array.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) ($data['id'] ?? $data['sub'] ?? 0),
            companyId: (int) ($data['company_id'] ?? 0),
            name: $data['name'] ?? '',
            email: $data['email'] ?? '',
            phone: $data['phone'] ?? null,
            avatar: $data['avatar'] ?? null,
            isActive: (bool) ($data['is_active'] ?? true),
            roles: $data['roles'] ?? [],
            permissions: $data['permissions'] ?? [],
        );
    }

    /**
     * Convert to array for serialization.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'company_id' => $this->companyId,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar,
            'is_active' => $this->isActive,
            'roles' => $this->roles,
            'permissions' => $this->permissions,
        ];
    }

    /**
     * Check if user has a specific role.
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles, true);
    }

    /**
     * Check if user has a specific permission.
     */
    public function hasPermission(string $permission): bool
    {
        // Super admin has all permissions
        if ($this->hasRole('super_admin')) {
            return true;
        }

        return in_array($permission, $this->permissions, true);
    }

    /**
     * Check if user has any of the given permissions.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        return !empty(array_intersect($permissions, $this->permissions));
    }

    /**
     * Check if user has all of the given permissions.
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if ($this->hasRole('super_admin')) {
            return true;
        }

        return empty(array_diff($permissions, $this->permissions));
    }
}
