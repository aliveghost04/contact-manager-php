<?php

namespace App\Repositories;

use \App\Database\Database;
use \App\Models\BaseModel;

class BaseRepository {
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
                $model = new $this->model();

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

    function save(BaseModel $model) {
        $fillable = array_keys(get_object_vars($model));

        if (empty($fillable)) {
            return true;
        }

        if ($model->{$this->primaryKey}) {
            $set = "";
            $params = [];
            foreach ($fillable as $key => $field) {
                if ($key >= 1) {
                    $set .= ', ';
                }

                $set .= "{$field} = ?";
                $params[] = $model->$field;
            }
            $params[] = $model->{$this->primaryKey};

            $this->database
                ->query(
                    "UPDATE {$this->table} SET {$set} WHERE {$this->primaryKey} = ?",
                    $params
                );
            
            return $model;
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
                if (is_null($model->$field)) {
                    $fields .= 'NULL';
                } else {
                    $fields .= '?';
                    $params[] = $model->$field;
                }
            }

            $this->database
                ->query(
                    "INSERT INTO {$this->table} ({$create}) VALUES ($fields)",
                    $params
                );
            
            $model->{$this->primaryKey} = $this->database->lastInsertedId();
            return $this;
        }
    }

    function delete($id = NULL) {
        $idToDelete = NULL;

        if ($id) {
            $idToDelete = $id;
        } elseif ($model->{$this->primaryKey}) {
            $idToDelete = $model->{$this->primaryKey};
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