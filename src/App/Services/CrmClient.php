<?php

namespace App\Services;

final class CrmClient
{
    /**
     * Sends a request to the CRM API.
     * 
     * @param array $config The CRM configuration.
     * @param string $endpoint The API endpoint.
     * @param array $data The data to send.
     * @return array The API response.
     */
    public static function request(array $config, string $endpoint, array $data): array
    {
        $url = rtrim($config['base_url'], '/') . '/' . ltrim($endpoint, '/');

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'token: ' . ($config['crm_token'] ?? ''),
                'Content-Type: application/json',
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            return ['status' => false, 'error' => 'CRM request failed: ' . $error];
        }

        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['status' => false, 'error' => 'Invalid JSON from CRM'];
        }

        return $decoded;
    }

    /**
     * Gets the client's IP address.
     *
     * @return string The client's IP address.
     */
    public static function getClientIp(): string
    {
        $keys = ['HTTP_X_FORWARDED_FOR', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];

        foreach ($keys as $key) {
            if (!empty($_SERVER[$key])) {
                $ipList = explode(',', $_SERVER[$key]);
                return trim($ipList[0]);
            }
        }

        return '0.0.0.0';
    }

    /**
     * Gets the landing page URL.
     * 
     * @return string The landing page URL.
     */
    public static function getLandingUrl(): string
    {
        if (!empty($_SERVER['HTTP_ORIGIN'])) {
            return $_SERVER['HTTP_ORIGIN'];
        }

        if (!empty($_SERVER['HTTP_REFERER'])) {
            return $_SERVER['HTTP_REFERER'];
        }

        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        return $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'unknown');
    }
}
