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
		//Morph into integer in case of boolean
		if ($newdefault === true)
			$newdefault = 1;
		else if ($newdefault === false)
			$newdefault = 0;

		//Set the actual value
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
        $this->attributes->key = $this->name();

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

    public function foreign ($foreignName = null) {
        if (!isset($this->attributes->foreign)) {
            $this->attributes->foreign                  = [];
			$this->attributes->foreign["id"]            = "id";
			$this->attributes->foreign["name"]			= $foreignName;
            $this->attributes->foreign["table"]         = $this->tablename;
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

        $this->attributes->foreign["DELETE_RULE"] = $method;

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

        $this->attributes->foreign["UPDATE_RULE"] = $method;

        //Always return self for concatenation
        return $this;
    }
}
