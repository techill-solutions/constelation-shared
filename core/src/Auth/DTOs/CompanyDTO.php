<?php

declare(strict_types=1);

namespace Constelation\Shared\Auth\DTOs;

/**
 * Data Transfer Object for company/tenant data.
 * 
 * This DTO represents company data that transits between services via JWT/session.
 * It does not depend on any database table - data comes from the Auth service.
 */
final class CompanyDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $document,
        public readonly ?string $email = null,
        public readonly ?string $phone = null,
        public readonly ?AddressDTO $address = null,
        public readonly bool $isActive = true,
        public readonly array $settings = [],
    ) {}

    /**
     * Create from JWT payload array.
     */
    public static function fromArray(array $data): self
    {
        $address = null;
        if (isset($data['address']) && is_array($data['address'])) {
            $address = AddressDTO::fromArray($data['address']);
        } elseif (isset($data['address_line1']) || isset($data['address'])) {
            $address = AddressDTO::fromArray($data);
        }

        return new self(
            id: (int) ($data['id'] ?? 0),
            name: $data['name'] ?? '',
            document: $data['document'] ?? $data['cnpj'] ?? '',
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            address: $address,
            isActive: (bool) ($data['is_active'] ?? true),
            settings: $data['settings'] ?? [],
        );
    }

    /**
     * Convert to array for serialization.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'document' => $this->document,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address?->toArray(),
            'is_active' => $this->isActive,
            'settings' => $this->settings,
        ];
    }

    /**
     * Get a specific setting value.
     */
    public function getSetting(string $key, mixed $default = null): mixed
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Check if company has a specific setting enabled.
     */
    public function hasSettingEnabled(string $key): bool
    {
        return (bool) ($this->settings[$key] ?? false);
    }
}
