<?php

namespace App\Services;

use App\Models\ContactModel as Contact;

class ContactValidator {

    private $contact;
    private $errors;

    function __construct(Contact $contact) {
        $this->contact = $contact;
        $this->errors = [];
    }

    function validate() {
        if (empty($this->contact->firstname)) {
            $this->errors['firstname'][] = 'Empty value';
        }

        if (strlen($this->contact->firstname) > 100) {
            $this->errors['firstname'][] = 'Max length is 100';
        }
        
        if (empty($this->contact->lastname)) {
            $this->errors['lastname'][] = 'Empty value';
        }
        
        if (strlen($this->contact->lastname) > 100) {
            $this->errors['lastname'][] = 'Max length is 100';
        }
        
        if (!filter_var($this->contact->email, FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'][] = 'Invalid email address';
        }

        if (strlen($this->contact->email) > 100) {
            $this->errors['email'][] = 'Max length is 100';
        }
        
        if (is_array($this->contact->phones) && !empty($this->contact->phones)) {
            foreach ($this->contact->phones as $phone) {
                if (!preg_match('/^[0-9]{10,20}$/', $phone)) {
                    $this->errors['phones'][$phone] = 'Invalid phone number';
                }
            }
        } elseif (empty($this->contact->phone)) {
            $this->errors['phones'][] = 'Empty value';
        } else {
            $this->errors['phones'][] = 'Invalid phone value';
        }
        
        return empty($this->errors) ? true : false;
    }

    function getErrors() {
        return $this->errors;
    }
}