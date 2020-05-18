<?php

namespace Aposoftworks\LOHM\Classes\Virtual;

//Interfaces

use Aposoftworks\LOHM\Classes\SyntaxLibrary;
use Illuminate\Contracts\Support\Jsonable;
use Aposoftworks\LOHM\Contracts\ToRawQuery;
use Illuminate\Contracts\Support\Arrayable;
use Aposoftworks\LOHM\Contracts\ComparableVirtual;

//Facades
use Illuminate\Support\Facades\DB;

class VirtualDatabase implements ToRawQuery, ComparableVirtual, Jsonable, Arrayable {

    /**
     * The real name of this database
     *
     * @var string
     */
    protected $databasename;

    /**
     * The tables that compose this database
     *
     * @var \Aposoftworks\LOHM\Classes\Virtual\VirtualTable array
     */
    protected $_tables;

    //-------------------------------------------------
    // Data methods
    //-------------------------------------------------

    public function name () {
        return $this->databasename;
    }
    public function tables () {
        return $this->_tables;
    }

    //-------------------------------------------------
    // Default methods
    //-------------------------------------------------

    public function __construct ($databasename, $tables = [], $valid = true) {
        $this->databasename = $databasename;
        $this->_tables      = $tables;
        $this->isvalid      = $valid;
    }

    public function isValid () {
        return $this->isvalid;
    }

    //-------------------------------------------------
    // Import methods
    //-------------------------------------------------

    public static function fromDatabase ($databasename) {
        $tablesraw      = collect(DB::select(SyntaxLibrary::getTables()));
        $tablesvirtual  = [];

        //We will consider a empty database as invalid
        if ($tablesraw->count() == 0) {
            return new VirtualDatabase($databasename, [], false);
        }

		//Fetch all tables
        for ($i = 0; $i < $tablesraw->count(); $i++) {
			$values = array_values((array)$tablesraw[$i]);
            $tablesvirtual[] = VirtualTable::fromDatabase($databasename, $values[0]);
        }

        return new VirtualDatabase($databasename, $tablesvirtual);
    }

    //-------------------------------------------------
    // Export methods
    //-------------------------------------------------

    public function toJson ($options = 0) {
        return json_encode($this->toArray, $options);
    }

    public function toQuery () {
        //Prepare columns
        $queryTables = [];

        for ($i = 0; $i < count($this->_tables); $i++) {
            $queryTables[] = $this->_tables[$i]->toQuery();
        }

        //Prepare general statement
        $raw = implode(" ", $queryTables);

        //Sanitize
        $raw = preg_replace("/\s+/", " ", $raw);
        $raw = trim($raw);

        return $raw;
    }

    public function toLateQuery () {
        //Prepare columns
        $queryTables = [];

        for ($i = 0; $i < count($this->_tables); $i++) {
            $queryTables[] = $this->_tables[$i]->toLateQuery();
        }

        //Prepare general statement
        $raw = implode(" ", $queryTables);

        //Sanitize
        $raw = preg_replace("/\s+/", " ", $raw);
        $raw = trim($raw);

        return $raw;
    }

    public function toArray () {
        $tables_as_arrays = [];

        for ($i = 0; $i < count($this->_tables); $i++) {
            $tables_as_arrays[] = $this->_tables[$i]->toArray();
        }

        //All data response
        return [
            "tables"        => $tables_as_arrays,
            "databasename"  => $this->databasename,
            "attributes"    => $this->attributes,
        ];
    }
}
