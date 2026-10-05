<?php

namespace App\Traits;

use App\Services\AuditLoggerService;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function ($model) {
            AuditLoggerService::log(
                action: strtolower(class_basename($model)).'.created',
                modelType: get_class($model),
                modelId: $model->getKey(),
                payload: $model->getAttributes()
            );
        });

        static::updated(function ($model) {
            AuditLoggerService::log(
                action: strtolower(class_basename($model)).'.updated',
                modelType: get_class($model),
                modelId: $model->getKey(),
                payload: [
                    'changes' => $model->getChanges(),
                    'original' => array_intersect_key($model->getOriginal(), $model->getChanges()),
                ]
            );
        });

        static::deleted(function ($model) {
            AuditLoggerService::log(
                action: strtolower(class_basename($model)).'.deleted',
                modelType: get_class($model),
                modelId: $model->getKey(),
                payload: $model->getAttributes()
            );
        });
    }

    public function logAudit(string $action, ?array $payload = null): void
    {
        AuditLoggerService::log(
            action: $action,
            modelType: get_class($this),
            modelId: $this->getKey(),
            payload: $payload
        );
    }
}
