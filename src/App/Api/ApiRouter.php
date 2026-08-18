<?php

namespace App\Api;

use App\Http\LeadController;
use App\Http\StatusController;

final class ApiRouter
{
    /** @var string */
    private string $action;

    /**
     * ApiRouter constructor.
     *
     * @param string $action The action to handle.
     */
    public function __construct(string $action = '')
    {
        $this->action = $action ?: $this->getAction();
    }

    /**
     * Dispatches the request to the appropriate controller based on the action.
     */
    public function dispatch(): array
    {
        return match($this->action) {
            'addlead' => $this->handleAddLead(),
            'getstatuses' => $this->handleGetStatuses(),
            default => ['status' => false, 'error' => 'Unknown action'],
        };
    }

    /**
     * Handles the 'addlead' action.
     *
     * @return array The result of the operation.
     */
    private function handleAddLead(): array
    {
        return LeadController::add();
    }

    /**
     * Handles the 'getstatuses' action.
     *
     * @return array The result of the operation.
     */
    private function handleGetStatuses(): array
    {
        return StatusController::index();
    }

    /**
     * Retrieves the action from the request, either from query parameters, form data, or JSON body.
     *
     * @return string The action to handle.
     */
    private function getAction(): string
    {
        if (isset($_GET['action'])) {
            return (string) $_GET['action'];
        }
        if (isset($_POST['action'])) {
            return (string) $_POST['action'];
        }
        
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (strpos($contentType, 'application/json') !== false) {
            $body = file_get_contents('php://input');
            $data = json_decode($body, true);
            if (is_array($data) && isset($data['action'])) {
                return (string) $data['action'];
            }
        }
        
        if (strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
            parse_str(file_get_contents('php://input'), $data);
            if (isset($data['action'])) {
                return (string) $data['action'];
            }
        }
        
        return basename($_SERVER['SCRIPT_FILENAME'], '.php');
    }

    /**
     * Static method to handle the API request.
     * 
     * @param string $action The action to handle.
     * @return array The result of the operation.
     */
    public static function handle(string $action): array
    {
        return (new self($action))->dispatch();
    }
    
}
