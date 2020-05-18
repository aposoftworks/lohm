<?php

namespace Aposoftworks\LOHM\Classes\Virtual;

//Interfaces

use Aposoftworks\LOHM\Classes\SyntaxLibrary;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Aposoftworks\LOHM\Contracts\ToRawQuery;
use Aposoftworks\LOHM\Contracts\ComparableVirtual;
use Exception;
//Facades
use Illuminate\Support\Facades\DB;
use stdClass;

class VirtualColumn implements ToRawQuery, ComparableVirtual, Jsonable, Arrayable {

    /**
     * The real name of the database that this column belongs to
     *
     * @var string
     */
    protected $databasename;

    /**
     * The real name of the database's table that this column belongs to
     *
     * @var string
     */
    protected $tablename;

    /**
     * The real name of the column
     *
     * @var string
     */
    protected $columnname;

    /**
     * Attributes that apply to this column
     *
     * @var array
     */
    protected $attributes;

    //-------------------------------------------------
    // Data methods
    //-------------------------------------------------

    public function name () {
        return $this->columnname;
	}

    public function table () {
        return $this->tablename;
    }

    public function attributes () {
        return $this->attributes;
    }

    //-------------------------------------------------
    // Static methods
    //-------------------------------------------------

    public static function sanitize ($column) {
        //Sort attributes
		$_preattributes     = [];

        //Type and size
        $splittype              = explode(" ", $column->Type);
        if (count($splittype) > 1) $_preattributes["unsigned"] = $splittype[1];
        $splittype              = explode("(", $splittype[0]);
        $_preattributes["type"] = $splittype[0];
		if (count($splittype) > 1) $_preattributes["length"] = preg_replace("/(\(|\))/", "", $splittype[1]);

		//Check for enum
		if (isset($_preattributes["length"]) && preg_match("/\,/", $_preattributes["length"])) {
			$_preattributes["length"] = explode(",", $_preattributes["length"]);
		}

        //Default value
        $_preattributes["default"] = $column->Default;

        //Key
        $_preattributes["key"] = $column->Key;

		//Nullable
        $_preattributes["nullable"] = $column->Null !== "NO";

        //Extra
        $_preattributes["extra"] = $column->Extra;

        return $_preattributes;
    }

    //-------------------------------------------------
    // Default methods
    //-------------------------------------------------

    public function __construct ($columnname, $attributes = [], $databasename = "", $tablename = "", $isvalid = true) {
        $this->databasename = $databasename;
        $this->tablename    = $tablename;
		$this->columnname   = $columnname;

		//Check for validity
		if (!is_null($attributes))
			$this->attributes   = (object)$attributes;
    }

    public function isValid () {
        return !is_null($this->attributes);
    }

    //-------------------------------------------------
    // Import methods
    //-------------------------------------------------

    public static function fromDatabase ($databasename, $tablename, $columnname) {
		//Try to find a column
		try {
			$column = (DB::select(SyntaxLibrary::getColumn($tablename, $columnname)))[0];
		}
		//No column found
		catch (\Exception $e) {
			return new VirtualColumn($columnname, null, $databasename, $tablename);
		}

        //Unset the name since we already got that in a specific property
		unset($column->Field);

		$_preattributes = VirtualColumn::sanitize((object)$column);

		//We need to get it's indexes, primaries and foreign keys
		$constraints = DB::select(SyntaxLibrary::checkIndex($columnname, $tablename));

		for ($i = 0; $i < count($constraints); $i++) {
			//Primary key
			if ($constraints[$i]->Key_name == "PRIMARY")
				$_preattributes["key"] = "PRI";
			//Unique
			else if ($constraints[$i]->Key_name == $constraints[$i]->Column_name)
				$_preattributes["key"] = "UNI";
			//Foreign key
			else {
				$name 				= $constraints[$i]->Key_name;

				//Try to get more info about the constraint
				try {
					$specificconstraint = DB::select(SyntaxLibrary::checkConstraint($name, $tablename))[0];
				}
				catch (\Exception $e) {
					throw new Exception("Foreign key name does not follow LOHM convention, could not retrieve enough info to rebuild it");
				}

				//Build foreign
				$foreign = [];

				try {
					$foreign["table"] 		= $specificconstraint->REFERENCED_TABLE_NAME;
					$foreign["connection"] 	= config("database.default");
					$foreign["id"]			= explode("_tc_", $name)[1];
				}
				catch (\Exception $e) {
					throw new Exception("Foreign key name does not follow LOHM convention, could not retrieve enough info to rebuild it");
				}

				//Conditional
				if ($specificconstraint->UPDATE_RULE != "RESTRICT") $foreign["UPDATE_RULE"] = $specificconstraint->UPDATE_RULE;
				if ($specificconstraint->DELETE_RULE != "RESTRICT") $foreign["DELETE_RULE"] = $specificconstraint->DELETE_RULE;

				//Index foreign
				$_preattributes["foreign"] = $foreign;
			}
		}

        return new VirtualColumn($columnname, $_preattributes, $databasename, $tablename);
    }

    //-------------------------------------------------
    // Export methods
    //-------------------------------------------------

    public function toJson ($options = 0) {
        return json_encode($this->toArray(), $options);
    }

    public function toQuery () {
		return SyntaxLibrary::column($this);
    }

    public function toLateQuery () {
		$queue = [];

		if (isset($this->attributes->foreign))
			$queue[] = SyntaxLibrary::createForeign($this);
		else if (isset($this->attributes->key) && $this->attributes->key == "UNI")
			$queue[] = SyntaxLibrary::createIndex($this);

		return $queue;
    }

    public function toArray () {
        return [
			"name" 			=> $this->columnname,
			"attributes" 	=> $this->attributes
		];
    }
}
