<?php

namespace App\Tenancy\Traits;

use App\Models\Organization;
use App\Tenancy\Scopes\OrganizationScope;
use App\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToOrganization
{
    /**
     * Boot the BelongsToOrganization trait for a model.
     */
    protected static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope(new OrganizationScope);

        static::creating(function ($model) {
            if (empty($model->organization_id) && TenantContext::check()) {
                $model->organization_id = TenantContext::getTenantId();
            }
        });
    }

    /**
     * Get the organization that owns the model.
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id');
    }

    /**
     * Scope a query to include all records regardless of organization.
     */
    public function scopeWithoutOrganization(Builder $query): Builder
    {
        return $query->withoutGlobalScope(OrganizationScope::class);
    }
}
