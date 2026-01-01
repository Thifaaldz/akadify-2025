<?php

namespace App\Models\Traits;

use App\Jobs\SendN8nWebhook;

trait TriggersN8n
{
    public static function bootTriggersN8n(): void
    {
        static::created(function ($model) {
            SendN8nWebhook::dispatch($model, 'created');
        });

        static::updated(function ($model) {
            SendN8nWebhook::dispatch($model, 'updated');
        });

        static::deleted(function ($model) {
            SendN8nWebhook::dispatch($model, 'deleted');
        });
    }
}
