<?php

namespace App\Controllers;

use \App\Controllers\BaseController;
use \App\Models\ContactModel as Contact;

class ContactController extends BaseController {

    function __construct() {
        parent::__construct();
        $this->setJSON();
    }

    private function validate($body) {
        $errors = [];

        if (empty($body->firstname)) {
            $errors['firstname'][] = 'Empty value';
        }

        if (strlen($body->firstname) > 100) {
            $errors['firstname'][] = 'Max length is 100';
        }
        
        if (empty($body->lastname)) {
            $errors['lastname'][] = 'Empty value';
        }
        
        if (strlen($body->lastname) > 100) {
            $errors['lastname'][] = 'Max length is 100';
        }
        
        if (!filter_var($body->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'Invalid email address';
        }
        
        if (is_array($body->phones) && !empty($body->phones)) {
            foreach ($body->phones as $phone) {
                if (!preg_match('/^[0-9]{10,20}$/', $phone)) {
                    $errors['phones'][$phone] = 'Invalid phone number';
                }
            }
        } elseif (empty($body->phone)) {
            $errors['phones'][] = 'Empty value';
        } else {
            $errors['phones'][] = 'Invalid phone value';
        }
        

        return empty($errors) ? true : $errors;
    }

    function getAll() {
        $contact = new Contact();
        $content = $contact->getAll();
        $this->response($content);
    }

    function get($params) {
        $id = $params['id'];

        $contact = new Contact();
        $content = $contact->get($id);

        if ($content) {
            $this->response($content);
        } else {
            $this->response(NULL, 404);
        }
    }

    function create() {
        $errors = $this->validate($this->body);

        if (is_array($errors)) {
            return $this->response($errors, 422);
        }

        $contact = new Contact();
        $contact = $contact->create($this->body);
        $this->response($contact, 201);
    }

    function delete($params) {
        $id = $params['id'];

        $contact = new Contact();
        $result = $contact->get($id);

        if (!$result) {
            return $this->response(NULL, 404);
        }
        
        if ($result->delete()) {
            $this->response($result);
        } else {
            $this->response([
                'error' => 'Cannot delete contact. Please try again later'
            ], 500);
        }
    }
}
