<?php

namespace App\Repositories;

use \App\Repositories\BaseRepository;
use \App\Models\ContactModel as Contact;

class ContactRepository extends BaseRepository {
    protected $table = 'contacts';
    protected $model = Contact::class;
    
    function delete($id = NULL) {
        try {
            if (is_null($id)) {
                return false;
            }
            
            $this->database->startTransaction();
            $this->database->query(
                "DELETE FROM contacts_phones WHERE contact_id = ?",
                [
                    $id
                ]
            );

            parent::delete($id);
            $this->database->commit();
            return true;
        } catch (\Exception $e) {
            $this->database->rollback();
            return false;
        } 
    }

    function create(Contact $contact) {
        try {
            $this->database->startTransaction();
            // Remove unneeded properties
            unset($contact->id);
            unset($contact->created_at);
            $phones = $contact->phones;
            unset($contact->phones);

            parent::save($contact);
            
            if (is_array($phones)) {
                foreach ($phones as $phone) {
                    $this->database->query(
                        'INSERT INTO contacts_phones (contact_id, number) VALUES (?, ?)',
                        [
                            $contact->{$this->primaryKey},
                            $phone
                        ]
                    );
                }
            }

            $this->database->commit();
            $contact = parent::get($contact->{$this->primaryKey});
            $contacts = [ $contact ];
            return $this->fillPhones($contacts)[0];
        } catch (\Exception $e) {
            $this->database->rollback();
            throw $e;
        }
    }

    function getAll() {
        $contacts = parent::getAll();
        return $this->fillPhones($contacts);
    }

    function get($id) {
        $contact = parent::get($id);
        
        if (is_null($contact)) {
            return NULL;
        } else {
            $contacts = [
                $contact
            ];
            return $this->fillPhones($contacts)[0];
        }
    }

    private function fillPhones($contacts) {
        $ids = array_map(function ($contact) {
            return $contact->{$this->primaryKey};
        }, $contacts);

        $placeholders = implode(', ', array_fill(0, count($ids), '?'));

        $contactsPhones = $this->database->query(
            "SELECT * FROM contacts_phones WHERE contact_id IN ({$placeholders})",
            $ids
        );

        $phones = [];
        foreach ($contactsPhones as $phone) {
            $phones[$phone['contact_id']][] = $phone;
        }

        foreach ($contacts as $contact) {
            $contact->phones = $phones[$contact->{$this->primaryKey}] ?? [];
        }

        return $contacts;
    }
}
