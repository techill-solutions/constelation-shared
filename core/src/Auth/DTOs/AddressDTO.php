<?php

declare(strict_types=1);

namespace Constelation\Shared\Auth\DTOs;

/**
 * Data Transfer Object for address data.
 * 
 * Provides a consistent address structure across all services.
 */
final class AddressDTO
{
    public function __construct(
        public readonly ?string $street = null,
        public readonly ?string $number = null,
        public readonly ?string $complement = null,
        public readonly ?string $neighborhood = null,
        public readonly ?string $city = null,
        public readonly ?string $state = null,
        public readonly ?string $zipCode = null,
        public readonly string $country = 'BR',
    ) {}

    /**
     * Create from array with various field naming conventions.
     */
    public static function fromArray(array $data): self
    {
        return new self(
            street: $data['street'] ?? $data['address'] ?? $data['address_line1'] ?? null,
            number: $data['number'] ?? $data['address_number'] ?? null,
            complement: $data['complement'] ?? $data['address_complement'] ?? $data['address_line2'] ?? null,
            neighborhood: $data['neighborhood'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            zipCode: $data['zip_code'] ?? $data['zipCode'] ?? $data['postal_code'] ?? null,
            country: $data['country'] ?? 'BR',
        );
    }

    /**
     * Convert to array for serialization.
     */
    public function toArray(): array
    {
        return [
            'street' => $this->street,
            'number' => $this->number,
            'complement' => $this->complement,
            'neighborhood' => $this->neighborhood,
            'city' => $this->city,
            'state' => $this->state,
            'zip_code' => $this->zipCode,
            'country' => $this->country,
        ];
    }

    /**
     * Get formatted full address string.
     */
    public function getFullAddress(): string
    {
        $parts = array_filter([
            $this->street,
            $this->number,
            $this->complement,
            $this->neighborhood,
            $this->city,
            $this->state,
            $this->zipCode,
        ]);

        return implode(', ', $parts);
    }

    /**
     * Get short address (street + number).
     */
    public function getShortAddress(): string
    {
        $parts = array_filter([$this->street, $this->number]);
        return implode(', ', $parts);
    }

    /**
     * Check if address has minimum required data.
     */
    public function isComplete(): bool
    {
        return !empty($this->street) && !empty($this->city) && !empty($this->state);
    }
}
