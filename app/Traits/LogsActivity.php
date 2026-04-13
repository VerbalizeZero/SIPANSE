<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            $model->logActivity('created');
        });

        static::updated(function ($model) {
            $model->logActivity('updated', $model->getOriginal());
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted', $model->getAttributes());
        });
    }

    public function logActivity(string $action, ?array $oldProperties = null): void
    {
        $properties = [];

        if ($oldProperties !== null) {
            $properties['old'] = $oldProperties;
        }

        if (in_array($action, ['created', 'updated'], true)) {
            $properties['new'] = $this->getAttributes();
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'log_name' => class_basename(static::class),
            'subject_type' => static::class,
            'subject_id' => $this->getKey(),
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
