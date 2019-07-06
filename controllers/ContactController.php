<?php

namespace App\Controllers;

use \App\Controllers\BaseController;
use \App\Models\ContactModel as Contact;
use \App\Repositories\ContactRepository;
use App\Services\ContactValidator;

class ContactController extends BaseController {
    private $contactRepository;

    function __construct() {
        parent::__construct();
        $this->setJSON();
        $this->contactRepository = new ContactRepository();
    }

    function getAll() {
        $contacts = $this->contactRepository->getAll();
        $this->response($contacts);
    }

    function get($params) {
        $id = $params['id'];

        $contact = $this->contactRepository->get($id);

        if ($contact) {
            $this->response($contact);
        } else {
            $this->response(NULL, 404);
        }
    }

    function create() {
        $contact = new Contact();
        $contact->fill($this->body);
        
        $contactValidator = new ContactValidator($contact);
        
        if (!$contactValidator->validate()) {
            $errors = $contactValidator->getErrors();
            return $this->response($errors, 422);
        }
        
        $contact = $this->contactRepository->create($contact);
        $this->response($contact, 201);
    }

    function delete($params) {
        $id = $params['id'];

        $contact = $this->contactRepository->get($id);
        
        if (is_null($contact)) {
            return $this->response(NULL, 404);
        }
        
        if ($this->contactRepository->delete($contact->id)) {
            $this->response($contact);
        } else {
            $this->response([
                'error' => 'Cannot delete contact. Please try again later'
            ], 500);
        }
    }
}
