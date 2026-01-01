<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Database\Eloquent\Model;

class SendN8nWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Model $model;
    public string $event;

    public function __construct(Model $model, string $event = 'created')
    {
        $this->model = $model;
        $this->event = $event;
    }

    public function handle(): void
    {
        $url = config('services.n8n.webhook_url') ?: env('N8N_WEBHOOK_URL');
        if (! $url) {
            return;
        }

        $payload = [
            'event' => $this->event,
            'model' => get_class($this->model),
            'id' => $this->model->getKey(),
            'attributes' => $this->model->getAttributes(),
        ];

        $body = json_encode($payload);

        $secret = config('services.n8n.webhook_secret') ?: env('N8N_WEBHOOK_SECRET');

        $headers = [];
        if ($secret) {
            $signature = hash_hmac('sha256', $body, $secret);
            $headers['X-N8N-Signature'] = $signature;
        }

        // best-effort post to n8n webhook
        try {
            Http::withHeaders($headers)
                ->timeout(10)
                ->post($url, $payload);
        } catch (\Throwable $e) {
            // Do not throw — keep fire-and-forget behaviour. Let the queue handle retries.
        }
    }
}
