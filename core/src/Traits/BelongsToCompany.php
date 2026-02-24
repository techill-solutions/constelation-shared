<?php

declare(strict_types=1);

namespace Constelation\Shared\Traits;

use Illuminate\Database\Eloquent\Builder;

trait BelongsToCompany
{
    protected static function bootBelongsToCompany(): void
    {
        static::addGlobalScope('company', function (Builder $query): void {
            $companyId = static::resolveCurrentCompanyId();

            if ($companyId !== null) {
                $query->where($query->getModel()->getTable() . '.company_id', $companyId);
            }
        });

        static::creating(function (mixed $model): void {
            if (!isset($model->company_id) || empty($model->company_id)) {
                $companyId = static::resolveCurrentCompanyId();

                if ($companyId !== null) {
                    $model->company_id = $companyId;
                }
            }
        });
    }

    public function scopeForCompany(Builder $query, int $companyId): Builder
    {
        return $query->withoutGlobalScope('company')
            ->where($this->getTable() . '.company_id', $companyId);
    }

    public function scopeWithoutCompanyScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('company');
    }

    protected static function resolveCurrentCompanyId(): ?int
    {
        try {
            $request = app('request');
        } catch (\Throwable) {
            return null;
        }

        if (!is_object($request)) {
            return null;
        }

        if (property_exists($request, 'attributes') && $request->attributes->has('company_id')) {
            return (int) $request->attributes->get('company_id');
        }

        if (is_callable([$request, 'hasHeader']) && is_callable([$request, 'header'])) {
            if ((bool) call_user_func([$request, 'hasHeader'], 'X-Company-ID')) {
                return (int) call_user_func([$request, 'header'], 'X-Company-ID');
            }
        }

        return null;
    }
}
