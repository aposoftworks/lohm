<?php

namespace Aposoftworks\LOHM\Classes\Concrete;

//Traits
use Illuminate\Support\Traits\Macroable;

//Classes
use Aposoftworks\LOHM\Classes\Virtual\VirtualTable;

class ConcreteTable extends VirtualTable {

    use Macroable;

    //-------------------------------------------------
    // Column types
    //-------------------------------------------------

    public function string ($name, $length = null) {
        $length             = is_null($length) ? config("lohm.default_database.string_size"):$length;
        $column             = new ConcreteColumn($name, ["type" => "varchar", "length" => $length], "", $this->tablename);
        $this->_columns[]   = $column;
        return $column;
    }

    public function text ($name, $length = null) {
        $column             = new ConcreteColumn($name, ["type" => "text", "length" => $length], "", $this->tablename);
        $this->_columns[]   = $column;
        return $column;
    }

    public function enum ($name, $options) {
        $column             = new ConcreteColumn($name, ["type" => "enum", "length" => $options], "", $this->tablename);
        $this->_columns[]   = $column;
        return $column;
    }

    public function integer ($name, $length = null) {
        $length             = is_null($length) ? config("lohm.default_database.integer_size"):$length;
        $column             = new ConcreteColumn($name, ["type" => "integer", "length" => $length], "", $this->tablename);
        $this->_columns[]   = $column;
        return $column;
    }

    public function bigInteger ($name, $length = null) {
        $length             = is_null($length) ? config("lohm.default_database.integer_size"):$length;
        $column             = new ConcreteColumn($name, ["type" => "BIGINT", "length" => $length], "", $this->tablename);
        $this->_columns[]   = $column;
        return $column;
    }

    public function binary ($name, $length = null) {
        $length             = is_null($length) ? config("lohm.default_database.binary_size"):$length;
        $column             = new ConcreteColumn($name, ["type" => "binary", "length" => $length], "", $this->tablename);
        $this->_columns[]   = $column;
        return $column;
    }

    public function boolean ($name) {
        $column             = new ConcreteColumn($name, ["type" => "integer", "length" => 1], "", $this->tablename);
        $this->_columns[]   = $column;
        return $column;
    }

    public function timestamp ($name) {
        $column             = new ConcreteColumn($name, ["type" => "timestamp"], "", $this->tablename);
        $this->_columns[]   = $column;
        return $column;
    }

    //-------------------------------------------------
    // Column helpers
    //-------------------------------------------------

    public function id ($name = "id") {
		if (config("lohm.default_database.id_type") == "integer")
			return $this->bigInteger($name)->increments()->primary();
		else
			return $this->sid($name);
    }

    public function sid ($name = "sid") {
		return $this->string($name, config("lohm.default_database.sid_size"))->primary();
    }

    public function uuid ($name = "uuid") {
		return $this->string($name, 36)->primary();
    }

    public function morphs ($name, $sid = false) {
        $this->string($name."_type");

        if ($sid)   $this->string($name."_id");
        else        $this->bigInteger($name."_id");
    }

    //-------------------------------------------------
    // Column collections
    //-------------------------------------------------

    public function timestamps ($createname = "date_created", $updatename = "date_updated", $deletename = "date_deleted") {
        $this->timestamp($createname);
        $this->timestamp($updatename)->nullable();
        $this->timestamp($deletename)->nullable();
    }
}
