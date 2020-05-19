<?php

namespace Aposoftworks\LOHM\Classes\Virtual;

//Interfaces

use Aposoftworks\LOHM\Classes\Helpers\DatabaseHelper;
use Aposoftworks\LOHM\Classes\SyntaxLibrary;
use Illuminate\Contracts\Support\Jsonable;
use Aposoftworks\LOHM\Contracts\ToRawQuery;
use Illuminate\Contracts\Support\Arrayable;
use Aposoftworks\LOHM\Contracts\ComparableVirtual;

//Facades
use Illuminate\Support\Facades\DB;

class VirtualTable implements ToRawQuery, ComparableVirtual, Jsonable, Arrayable {

    /**
     * In case you created this from a database source, checks if the table exists and is valid
     *
     * @var boolean
     */
    protected $isvalid = true;

    /**
     * The real name of the database that this column belongs to
     *
     * @var string
     */
    protected $databasename;

    /**
     * The real name of this table
     *
     * @var string
     */
    protected $tablename;

    /**
     * The columns that compose this table
     *
     * @var \Aposoftworks\LOHM\Classes\Virtual\VirtualColumn[]
     */
    protected $_columns;

    //-------------------------------------------------
    // Data methods
    //-------------------------------------------------

    public function name () {
        return $this->tablename;
    }

    public function database () {
        return $this->databasename;
    }

    public function columns () {
        return $this->_columns;
    }

    public function dataColumns () {
        $response = [];

        for ($i = 0; $i < count($this->_columns); $i++) {
            $column = $this->_columns[$i];

            $response[$column->name()] = [
                "order"     => $i,
                "column"    => $column,
            ];
        }

        return $response;
    }

    //-------------------------------------------------
    // Default methods
    //-------------------------------------------------

    public function __construct ($tablename, $columns = [], $databasename = "", $valid = true) {
        $this->databasename = $databasename;
        $this->tablename    = $tablename;
        $this->_columns     = $columns;
        $this->isvalid      = $valid;
    }

    public function isValid () {
        return $this->isvalid;
    }

    //-------------------------------------------------
    // Import methods
    //-------------------------------------------------

    public static function fromDatabase ($databasename, $tablename) {
		$string = DatabaseHelper::getTableCreation($tablename);
		$table 	= DatabaseHelper::buildTable($string);

        return $table;
    }

    //-------------------------------------------------
    // Export methods
    //-------------------------------------------------

    public function toJson ($options = 0) {
        return json_encode($this->toArray(), $options);
    }

    public function toQuery () {
        return SyntaxLibrary::createTable($this);
    }

    public function toLateQuery () {
        //Prepare columns
        $queryColumns = [];

        for ($i = 0; $i < count($this->_columns); $i++) {
            $query = $this->_columns[$i]->toLateQuery();

            for ($x = 0; $x < count($query);$x++) {
                if ($query[$x] !== "") $queryColumns[] = $query[$x];
            }
        }

        return $queryColumns;
    }

    public function toArray() {
        //Get all columns as array
        $columns_as_arrays = [];

        for ($i = 0; $i < count($this->_columns); $i++) {
            $columns_as_arrays[] = $this->_columns[$i]->toArray();
        }

        //All data response
        return [
            "name"		=> $this->tablename,
            "columns"	=> $columns_as_arrays,
        ];
    }
}
