<?php

namespace App\Models;

use \App\Models\BaseModel;

class ContactModel extends BaseModel {
    protected $table = 'contacts';
    protected $fillable = [
        'firstname',
        'lastname',
        'email'
    ];

    function delete($id = NULL) {
        $idToDelete = NULL;

        try {
            if ($id) {
                $idToDelete = $id;
            } elseif ($this->{$this->primaryKey}) {
                $idToDelete = $this->{$this->primaryKey};
            } else {
                return false;
            }
            
            $this->database->startTransaction();
            $this->database->query(
                "DELETE FROM contacts_phones WHERE contact_id = ?",
                [
                    $idToDelete
                ]
            );
            parent::delete($idToDelete);
            $this->database->commit();
            return true;
        } catch (\Exception $e) {
            $this->database->rollback();
            return false;
        } 
    }

    function create($data) {
        $contact = new ContactModel();

        foreach ($this->fillable as $value) {
            $contact->$value = $data->$value;
        }

        try {
            $this->database->startTransaction();
            $contact->save();
            
            foreach ($data->phones as $phone) {
                $this->database->query(
                    "INSERT INTO contacts_phones (contact_id, number) VALUES (?, ?)",
                    [
                        $contact->{$this->primaryKey},
                        $phone
                    ]
                );
            }

            $this->database->commit();
            return $contact;
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
        $contacts = [
            $contact
        ];
        return $this->fillPhones($contacts)[0];
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
