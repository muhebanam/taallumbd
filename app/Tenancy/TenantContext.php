<?php

namespace App\Tenancy;

use App\Models\Organization;

class TenantContext
{
    protected static ?Organization $currentTenant = null;

    /**
     * Set the current active tenant organization.
     */
    public static function setTenant(?Organization $tenant): void
    {
        static::$currentTenant = $tenant;
    }

    /**
     * Get the active tenant organization.
     */
    public static function getTenant(): ?Organization
    {
        return static::$currentTenant;
    }

    /**
     * Get the ID of the current tenant organization.
     */
    public static function getTenantId(): ?int
    {
        return static::$currentTenant?->id;
    }

    /**
     * Check if a tenant organization is currently bound.
     */
    public static function check(): bool
    {
        return static::$currentTenant !== null;
    }

    /**
     * Clear the current tenant context.
     */
    public static function clear(): void
    {
        static::$currentTenant = null;
    }

    /**
     * Alias for clear().
     */
    public static function clearTenant(): void
    {
        static::clear();
    }
}
