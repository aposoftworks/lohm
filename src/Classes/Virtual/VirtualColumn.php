<?php

namespace Aposoftworks\LOHM\Classes\Virtual;

//Interfaces
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Aposoftworks\LOHM\Contracts\ToRawQuery;
use Aposoftworks\LOHM\Contracts\ComparableVirtual;

//Facades
use Illuminate\Support\Facades\DB;

//General
use Exception;
use Aposoftworks\LOHM\Classes\SyntaxLibrary;
use Aposoftworks\LOHM\Traits\ColumnSanitizer;
use Aposoftworks\LOHM\Traits\ConstraintSanitizer;

class VirtualColumn implements ToRawQuery, ComparableVirtual, Jsonable, Arrayable {

    //-------------------------------------------------
    // Traits
    //-------------------------------------------------

	use ColumnSanitizer;
	use ConstraintSanitizer;

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
			$_preattributes = $_preattributes + VirtualColumn::sanitizeConstraint($constraints[$i], $tablename);
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

		if (isset($this->attributes->foreign)) {
			$queue[] = SyntaxLibrary::createForeign($this);
		}
		else if (isset($this->attributes->key) && $this->attributes->key != "PRI") {
			if ($this->attributes->key === "UNI")
				$queue[] = SyntaxLibrary::createIndex($this);
			else
				$queue[] = SyntaxLibrary::createIndex($this, $this->table(), $this->attributes->key);
		}

		return $queue;
    }

    public function toArray () {
        return [
			"name" 			=> $this->columnname,
			"attributes" 	=> $this->attributes
		];
    }
}
