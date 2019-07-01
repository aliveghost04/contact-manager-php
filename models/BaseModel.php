<?php

namespace App\Models;

use \App\Database\Database;

class BaseModel {
    protected $database;
    protected $primaryKey;

    function __construct() {
        $this->database = Database::getInstance();
        $this->primaryKey = 'id';

        if (!$this->table) {
            $classname = get_class($this);
            throw new \Exception("Table name is required on \"$classname\"");
        }
    }

    function getAll() {
        $results = $this->database
            ->query("SELECT * FROM {$this->table}");
        return $this->parse($results);
    }

    function get($id) {
        $results = $this->database
            ->query(
                "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ?",
                [
                    $id
                ]
            );
        
        $results = $this->parse($results);
        return $results[0];
    }

    private function parse($data) {
        if (is_array($data)) {
            foreach ($data as $value) {
                $array = array_keys($value);
                $class = get_class($this);
                $model = new $class();

                foreach ($array as $key) {
                    $model->$key = $value[$key];
                }

                $return[] = $model;
            }
            return $return;
        } else {
            return $data;
        }
    }

    function save() {
        $fillable = $this->fillable ?? [];

        if (empty($fillable)) {
            return true;
        }

        if ($this->{$this->primaryKey}) {
            $set = "";
            $params = [];
            foreach ($fillable as $key => $field) {
                if ($key >= 1) {
                    $set .= ', ';
                }

                $set .= "{$field} = ?";
                $params[] = $this->$field;
            }
            $params[] = $this->{$this->primaryKey};

            $this->database
                ->query(
                    "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = ?",
                    $params
                );
            
            return $this;
        } else {
            $create = "";
            $fields = "";
            $params = [];
            foreach ($fillable as $key => $field) {
                if ($key >= 1) {
                    $create .= ', ';
                    $fields .= ', ';
                }

                $create .= $field;
                if (is_null($this->$field)) {
                    $fields .= 'NULL';
                } else {
                    $fields .= '?';
                    $params[] = $this->$field;
                }
            }

            $this->database
                ->query(
                    "INSERT INTO {$this->table} ({$create}) VALUES ($fields)",
                    $params
                );
            
            $this->{$this->primaryKey} = $this->database->lastInsertedId();
            return $this;
        }
    }

    function delete($id = NULL) {
        $idToDelete = NULL;

        if ($id) {
            $idToDelete = $id;
        } elseif ($this->{$this->primaryKey}) {
            $idToDelete = $this->{$this->primaryKey};
        } else {
            return false;
        }
        
        return $this->database
            ->query(
                "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?",
                [
                    $idToDelete
                ]
            );
    }
}