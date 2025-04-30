<?php

abstract class BaseController {
    protected $db;
    protected $requestMethod;
    protected $response = [
        'status_code' => 200,
        'body' => null
    ];

    public function __construct($db, $requestMethod) {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
    }

    abstract public function processRequest();

    protected function setResponse($statusCode, $data = null, $message = null) {
        $this->response['status_code'] = $statusCode;
        
        if ($data !== null || $message !== null) {
            $this->response['body'] = json_encode([
                'status' => $statusCode >= 200 && $statusCode < 300 ? 'success' : 'error',
                'data' => $data,
                'message' => $message
            ]);
        }
    }

    protected function sendResponse() {
        if (isset($this->response['status_code'])) {
            http_response_code($this->response['status_code']);
        }
        if ($this->response['body']) {
            echo $this->response['body'];
        }
    }

    protected function validateRequiredFields($input, $requiredFields) {
        foreach ($requiredFields as $field) {
            if (!isset($input[$field]) || empty($input[$field])) {
                return false;
            }
        }
        return true;
    }

    protected function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map(function($value) {
                return is_string($value) ? htmlspecialchars(strip_tags($value)) : $value;
            }, $input);
        }
        return htmlspecialchars(strip_tags($input));
    }
} 