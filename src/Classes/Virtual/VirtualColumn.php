<?php

namespace Aposoftworks\LOHM\Classes\Virtual;

//Interfaces
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Aposoftworks\LOHM\Contracts\ToRawQuery;
use Aposoftworks\LOHM\Contracts\ComparableVirtual;

//Facades
use Illuminate\Support\Facades\DB;

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

    //-------------------------------------------------
    // Static methods
    //-------------------------------------------------

    public static function extra ($extra) {
        switch ($extra) {
            case  "auto_increment":
                return "AUTO_INCREMENT";
            default:
                return "";
        }
    }

    public static function sanitize ($column) {
        //Sort attributes
        $_preattributes     = [];

        //Type and size
        $splittype              = explode(" ", $column->Type);
        if (count($splittype) > 1) $_preattributes["unsigned"] = $splittype[1];
        $splittype              = explode("(", $splittype[0]);
        $_preattributes["type"] = $splittype[0];
        if (count($splittype) > 1) $_preattributes["length"] = preg_replace("/(\(|\))/", "", $splittype[1]);

        //Default value
        $_preattributes["default"] = $column->Default;

        //Key
        $_preattributes["key"] = $column->Key;

        //Nullable
        $_preattributes["nullable"] = $column->Null === "NO" ? "NOT NULL":"NULL";

        //Extra
        $_preattributes["extra"] = VirtualColumn::extra($column->Extra);

        return $_preattributes;
    }

    //-------------------------------------------------
    // Default methods
    //-------------------------------------------------

    public function __construct ($columnname, $attributes = [], $databasename = "", $tablename = "") {
        $this->databasename = $databasename;
        $this->tablename    = $tablename;
        $this->columnname   = $columnname;
        $this->attributes   = (object)$attributes;
    }

    public function isValid () {
        return !is_null($this->attributes);
    }

    public function buildType () {
        if (isset($this->attributes->length)) {
            $length = $this->attributes->length;

            //Check for enums
            if (is_array($length)) {
                $length = array_map(function ($value) { return "'".$value."'"; }, $length);
                $length = implode (", ", $length);
            }

            return $this->attributes->type."(".$length.")".(isset($this->attributes->unsigned)? " UNSIGNED":"");
        }
        else
            return $this->attributes->type;
    }

    //-------------------------------------------------
    // Import methods
    //-------------------------------------------------

    public static function fromDatabase ($databasename, $tablename, $columnname) {
        $allcolumns = collect(DB::select("SHOW COLUMNS FROM ".$databasename.".".$tablename));
        $column     = $allcolumns->filter(function ($value) use ($columnname) {
            return $value->Field === $columnname;
        })->first();

        //Unset the name since we already got that in a specific property
        unset($column->Field);

        $_preattributes = VirtualColumn::sanitize($column);

        return new VirtualColumn($columnname, $_preattributes, $databasename, $tablename);
    }

    //-------------------------------------------------
    // Export methods
    //-------------------------------------------------

    public function toJson ($options = 0) {
        return json_encode($this->toArray(), $options);
    }

    public function toQuery () {
        //Configuration
        $name       = $this->columnname;
        $type       = $this->buildType();
        $increment  = isset($this->attributes->extra)? $this->attributes->extra:"";
        $nullable   = isset($this->attributes->nullable) && $this->attributes->nullable === true? "NULL":"NOT NULL";
        $primary    = isset($this->attributes->key) && $this->attributes->key == "PRI"? "PRIMARY KEY":"";
		$default    = isset($this->attributes->default)? ("DEFAULT '".$this->attributes->default."'"):"";
		$unsigned 	= isset($this->attributes->usnigned)? "UNSIGNED":"";

        //Sanitization
        $raw = implode(" ", [$name, $type, $increment, $nullable, $default, $primary, $unsigned]);
        $raw = preg_replace("/\s+/", " ", $raw);
        $raw = trim($raw);

        return $raw;
    }

    public function toLateQuery () {
        $query = [];

        //Foreign keys
        if (isset($this->attributes->foreign)) {
            $name           = $this->columnname."_".$this->tablename."_".$this->attributes->foreign["table"];
            $database_conn  = config("database.connections.".$this->attributes->foreign["connection"].".database");

            $prequery  = "ALTER TABLE ".$this->tablename;
            $prequery .= " ADD CONSTRAINT ".$name." FOREIGN KEY (".$this->columnname.") ";
            $prequery .= "REFERENCES ".$this->attributes->foreign["table"]." (".$this->attributes->foreign["id"].")";
            $prequery .= isset($this->attributes->foreign["method"]) ? $this->attributes->foreign["method"]:"";
            $query[] = $prequery;
        }

        //Unique index
        if (isset($this->attributes->key) && $this->attributes->key == "UNI")
            $query[] = "CREATE INDEX ".$this->tablename."_".$this->columnname."in ON ".$this->tablename." (".$this->columnname.")";

        //Return data
        return $query;
    }

    public function toArray () {
        return ["columnname" => $this->columnname, "attributes" => $this->attributes];
    }
}
