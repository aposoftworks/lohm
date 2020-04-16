<?php

namespace Aposoftworks\LOHM\Classes\Concrete;

//Traits
use Illuminate\Support\Traits\Macroable;

//Classes
use Aposoftworks\LOHM\Classes\Virtual\VirtualColumn;

class ConcreteColumn extends VirtualColumn {

    use Macroable;

    //-------------------------------------------------
    // General types
    //-------------------------------------------------

    public function length ($newlength) {
        $this->attributes->length = $newlength;

        //Always return self for concatenation
        return $this;
    }

    public function default ($newdefault = null) {
        $this->attributes->default = $newdefault;

        //Always return self for concatenation
        return $this;
    }

    public function nullable ($bool = true) {
        $this->attributes->nullable = $bool;

        //Always return self for concatenation
        return $this;
    }

    public function unique () {
        $this->attributes->key = "UNI";

        //Always return self for concatenation
        return $this;
    }

    public function primary () {
        $this->attributes->key = "PRI";

        //Always return self for concatenation
        return $this;
    }

    public function increments () {
        $this->attributes->extra = "AUTO_INCREMENT";

        //Always return self for concatenation
        return $this;
    }

    public function unsigned () {
		$this->attributes->unsigned = true;

        //Always return self for concatenation
        return $this;
    }

    //-------------------------------------------------
    // Foreign types
    //-------------------------------------------------

    public function foreign ($otherTable = null) {
        if (!isset($this->attributes->foreign)) {
            $this->attributes->foreign                  = [];
            $this->attributes->foreign["id"]            = "id";
            $this->attributes->foreign["table"]         = is_null($otherTable) ? $this->tablename:$otherTable;
            $this->attributes->foreign["connection"]    = config("database.default");
        }

        //Always return self for concatenation
        return $this;
    }

    public function references ($idOfOtherTable = "id") {
        if (!isset($this->attributes->foreign)) {
            $this->attributes->foreign                  = [];
            $this->attributes->foreign["table"]         = $this->tablename;
            $this->attributes->foreign["connection"]    = config("database.default");
        }

        $this->attributes->foreign["id"]    = $idOfOtherTable;

        //Always return self for concatenation
        return $this;
    }

    public function on ($otherTable, $connection = null) {
        if (is_null($connection)) {
            $connection = config("database.default");
        }

        if (!isset($this->attributes->foreign)) {
            $this->attributes->foreign          = [];
            $this->attributes->foreign["id"]    = "id";
        }

        $this->attributes->foreign["table"]         = $otherTable;
        $this->attributes->foreign["connection"]    = $connection;

        //Always return self for concatenation
        return $this;
    }

    public function onDelete($method = "CASCADE") {
        if (!isset($this->attributes->foreign)) {
            $this->attributes->foreign                  = [];
            $this->attributes->foreign["id"]            = "id";
            $this->attributes->foreign["table"]         = $this->tablename;
            $this->attributes->foreign["connection"]    = config("database.default");
        }

        $this->attributes->foreign["method"] = " ON DELETE ".$method;

        //Always return self for concatenation
        return $this;
    }

    public function onUpdate($method = "CASCADE") {
        if (!isset($this->attributes->foreign)) {
            $this->attributes->foreign                  = [];
            $this->attributes->foreign["id"]            = "id";
            $this->attributes->foreign["table"]         = $this->tablename;
            $this->attributes->foreign["connection"]    = config("database.default");
        }

        $this->attributes->foreign["method"] = " ON UPDATE ".$method;

        //Always return self for concatenation
        return $this;
    }
}
