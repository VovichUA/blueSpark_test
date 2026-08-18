<?php

namespace App\Http;

use App\Config\Config;
use App\Services\CrmClient;

final class LeadController
{
    /**
     * Handles the addition of a new lead.
     * 
     * @return array The result of the operation.
     */
    public static function add(): array
    {
        $config = Config::load();

        $input = $_POST;
        if (empty($input)) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $input = $decoded;
            }
        }

        $firstName = trim((string) ($input['firstName'] ?? ''));
        $lastName  = trim((string) ($input['lastName'] ?? ''));
        $phone     = trim((string) ($input['phone'] ?? ''));
        $email     = trim((string) ($input['email'] ?? ''));

        $errors = [];
        if ($firstName === '') $errors[] = 'firstName is required';
        if ($lastName === '') $errors[] = 'lastName is required';
        if ($phone === '') $errors[] = 'phone is required';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'valid email is required';
        }

        if (!empty($errors)) {
            http_response_code(422);
            return ['status' => false, 'error' => implode(', ', $errors)];
        }

        $payload = [
            'firstName'   => $firstName,
            'lastName'    => $lastName,
            'phone'       => $phone,
            'email'       => $email,
            'countryCode' => $config['country_code'],
            'box_id'      => $config['box_id'],
            'offer_id'    => $config['offer_id'],
            'landingUrl'  => CrmClient::getLandingUrl(),
            'ip'          => CrmClient::getClientIp(),
            'password'    => $config['crm_password'],
            'language'    => $config['language'],
        ];

        return CrmClient::request($config, 'addlead', $payload);
    }
    
}
