<?php

namespace App\Http;

use App\Config\Config;
use App\Services\CrmClient;

final class StatusController
{
    /**
     * Handles the retrieval of lead statuses.
     * 
     * @return array The result of the operation.
     */
    public static function index(): array
    {
        $config = Config::load();

        $input = $_SERVER['REQUEST_METHOD'] === 'GET' ? $_GET : $_POST;
        if (empty($input)) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $input = $decoded;
            }
        }

        $dateFrom = trim((string) ($input['date_from'] ?? ''));
        $dateTo   = trim((string) ($input['date_to'] ?? ''));
        $page     = isset($input['page']) ? (int) $input['page'] : 0;
        $limit    = isset($input['limit']) ? (int) $input['limit'] : 100;

        if ($limit > 500) $limit = 500;
        if ($limit <= 0) $limit = 100;
        if ($page < 0) $page = 0;

        $payload = ['page' => $page, 'limit' => $limit];
        if ($dateFrom !== '') $payload['date_from'] = $dateFrom;
        if ($dateTo !== '') $payload['date_to'] = $dateTo;

        return CrmClient::request($config, 'getstatuses', $payload);
    }
}
