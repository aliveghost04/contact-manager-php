<?php

namespace App\Controllers;

class BaseController {
    private $isJSON;
    protected $body;

    function __construct() {
        $this->isJSON = false;
        $this->parseJSONBody();
    }

    function response($content, $status = 200, $headers = []) {
        
        http_response_code($status);
        if (is_array($headers)) {
            foreach ($headers as $head => $value) {
                header("{$key}: $value");
            }
        }
        
        if ($this->isJSON) {
            header('Content-Type: application/json');
            echo $content ? json_encode($content) : NULL;
        } else {
            echo $content;
        }
    }

    function setJSON() {
        $this->isJSON = true;
    }

    private function parseJSONBody() {
        $contentType = strtolower($_SERVER['HTTP_CONTENT_TYPE']);

        if ($contentType === 'application/json') {
            $this->body = json_decode(file_get_contents('php://input'));
        }
    }
}
