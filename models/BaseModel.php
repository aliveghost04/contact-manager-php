<?php

namespace App\Models;

class BaseModel {
    
    public function fill($object) {
        $props = get_object_vars($this);

        foreach ($props as $prop => $value) {
            $this->$prop = $object->$prop;
        }
    }
}
