<?php

namespace LimonLabs\Bigcommerce\Libraries\Bigcommerce;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Throwable;

class BcClient
{
    protected string $baseUrl;
    protected array $headers;

    public function __construct($token = '')
    {
        $this->baseUrl = config('services.bigcommerce.url');
        $this->headers = [
            'X-Auth-Token' => $token ?: config('services.bigcommerce.token'),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    public function request(string $method, string $endpoint, array $options = [])
    {
        try {
            $response = Http::withHeaders($this->headers)
                ->{$method}($this->baseUrl . $endpoint, $options);

            if ($response->failed()) {
                $this->logError("BC API Error", $endpoint, $response);
            }

            return $response;
        } catch (Throwable $e) {
            $this->logException("BC API Exception", $endpoint, $e);
            throw $e; // Optionally rethrow
        }
    }

    protected function logError(string $message, string $endpoint, $response)
    {
        Log::channel('bigcommerce')->error($message, [
            'endpoint' => $endpoint,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);
    }

    protected function logException(string $message, string $endpoint, Throwable $e)
    {
        Log::channel('bigcommerce')->error($message, [
            'endpoint' => $endpoint,
            'exception' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}