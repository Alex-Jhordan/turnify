<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IdentityLookupService
{
    public function lookup(string $documentType, string $documentNumber): ?string
    {
        $endpoint = config('services.identity_api.base_url');
        $token = config('services.identity_api.token');

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(5)
                ->get("{$endpoint}/lookup", [
                    'type' => $documentType,
                    'number' => $documentNumber,
                ]);

            if ($response->successful()) {
                return $response->json('full_name') ?? $response->json('name');
            }

            Log::warning("Identity lookup failed [{$response->status()}]: " . $response->body());
            return null;

        } catch (\Throwable $e) {
            Log::error("IdentityLookupService error: " . $e->getMessage());
            return null;
        }
    }
}
