<?php

namespace App\Config;

use Dotenv\Dotenv;

final class Config
{
    public static function load(): array
    {
        $envPath = __DIR__ . '/../../';

        if (is_file($envPath . '.env')) {
            $dotenv = Dotenv::createImmutable($envPath);
            $dotenv->safeLoad();
        }

        return [
            'crm_token'     => $_ENV['CRM_TOKEN'] ?? '',
            'crm_password'  => $_ENV['CRM_PASSWORD'] ?? '',
            'box_id'        => (int) ($_ENV['CRM_BOX_ID'] ?? 0),
            'offer_id'      => (int) ($_ENV['CRM_OFFER_ID'] ?? 0),
            'country_code'  => $_ENV['CRM_COUNTRY_CODE'] ?? 'GB',
            'language'      => $_ENV['CRM_LANGUAGE'] ?? 'en',
            'base_url'      => rtrim($_ENV['CRM_BASE_URL'] ?? '', '/'),
        ];
    }
}
