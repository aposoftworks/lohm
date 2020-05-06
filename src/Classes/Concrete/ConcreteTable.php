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

    public function char ($name, $length = null) {
        $length             = is_null($length) ? config("lohm.default_database.string_size"):$length;
        $column             = new ConcreteColumn($name, ["type" => "char", "length" => $length], "", $this->tablename);
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

    public function morphs ($name, $sid = null) {
        $this->string($name."_type");

		if (is_null($sid))	{
			if (config("lohm.default_database.id_type") == "string")
				$this->string($name."_id", config("lohm.default_database.sid_size"));
			else
				$this->bigInteger($name."_id", config("lohm.default_database.id_size"));
		}
        else if ($sid)   	$this->string($name."_id", config("lohm.default_database.sid_size"));
        else        		$this->bigInteger($name."_id", config("lohm.default_database.id_size"));
	}

	public function foreign ($name, $tablename) {
		if (config("lohm.default_database.id_type") == "string")
			return $this->string($name, config("lohm.default_database.sid_size"))->foreign($tablename);
		else
			return $this->bigInteger($name, config("lohm.default_database.id_size"))->unsigned()->foreign($tablename);
	}

    //-------------------------------------------------
    // Column helpers - primary
    //-------------------------------------------------

    public function id ($name = null) {
		$name = is_null($name) ? config("lohm.default_naming.id") : $name;

		if (config("lohm.default_database.id_type") == "string")
			return $this->sid($name);
		else
			return $this->bigInteger($name, config("lohm.default_database.id_size"))->increments()->primary()->unsigned();
    }

    public function sid ($name = null) {
		$name = is_null($name) ? config("lohm.default_naming.sid") : $name;

		return $this->string($name, config("lohm.default_database.sid_size"))->primary();
    }

    public function uuid ($name = null) {
		$name = is_null($name) ? config("lohm.default_naming.uuid") : $name;

		return $this->string($name, 36)->primary();
    }

    //-------------------------------------------------
    // Column collections
    //-------------------------------------------------

    public function timestamps ($createname = null, $updatename = null, $deletename = null) {
		$name_create = is_null($createname) ? config("lohm.default_naming.date_created") : $createname;
		$name_update = is_null($createname) ? config("lohm.default_naming.date_updated") : $updatename;
		$name_delete = is_null($createname) ? config("lohm.default_naming.date_deleted") : $deletename;

        $this->timestamp($name_create);
		$this->timestamp($name_update)->nullable();

		//Optional field
		if (config("lohm.soft_deletes") === true || !is_null($deletename))
        	$this->timestamp($name_delete)->nullable();
    }
}
