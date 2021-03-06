<?php

namespace Aposoftworks\LOHM\Classes;

//Laravel
use Illuminate\Support\Facades\DB;

//Helpers
use Aposoftworks\LOHM\Classes\Helpers\QueryHelper;
use Aposoftworks\LOHM\Classes\Virtual\VirtualTable;
use Aposoftworks\LOHM\Classes\Concrete\ConcreteTable;
use Aposoftworks\LOHM\Classes\Helpers\DatabaseHelper;

class LOHM {
	//General
    protected $method;
    protected $connection;

	//Queries
    protected $_queues        	= [];
	protected $_latequeues    	= [];

	//Static
	protected static $classes 	= [];

    //-------------------------------------------------
    // Main methods
    //-------------------------------------------------

    public function __construct () {
        $this->connection = config("database.default");
    }

    //-------------------------------------------------
    // Data methods
    //-------------------------------------------------

    public function queues () {
        return $this->_queues;
    }

    public function latequeues () {
        return $this->_latequeues;
    }

    //-------------------------------------------------
    // Create methods
    //-------------------------------------------------

    public function table ($name, $callback) {
        $table = new ConcreteTable($name);
        $callback($table);

        $this->enqueuer($table->toQuery(),      $table, "_queues");
        $this->enqueuer($table->toLateQuery(),  $table, "_latequeues");

        //Reset connection after change
        $this->connection = config("database.default");
    }

    public function dropTable ($name) {
        $this->_latequeues[] = ["conn" => $this->connection, "table" => $name, "type" => "droptable"];
    }

    public function conn ($name) {
        $this->connection = $name;

        return $this;
    }

    public function existsTable ($name) {
        $exists = DB::connection($this->connection)->select(QueryHelper::checkTable($name));

        return count($exists) === 1;
    }

    //-------------------------------------------------
    // Migration methods
    //-------------------------------------------------

    public function queue ($filepath, $method = "up") {
		$this->method = $method;

		if (isset(static::$classes[$filepath])) {
			$class = static::$classes[$filepath];
		}
		else {
			//Actually require
			require_once $filepath;

			//Get class name
			$classes 	= get_declared_classes();
			$class 		= end($classes);

			//Set class
			static::$classes[$filepath] = $class;
		}

        //Instanceit
        $class = new $class();

        if($method === "up")
            $class->up();
        else
            $class->down();
    }

    public function migrate () {
        //Merge the two queues and turn into a collection
        $queues = [];

        for ($i = 0; $i < count($this->_queues); $i++) {
            $data = $this->_queues[$i];

            if (trim($data["query"]) != "") {
                $queues[] = function () use ($data) {
                    return $this->migrationRun($data);
                };
            }
        }

        for ($i = 0; $i < count($this->_latequeues); $i++) {
            $data = $this->_latequeues[$i];

            if (trim($data["query"]) != "") {
                $queues[] = function () use ($data) {
                    return $this->migrationRun($data);
                };
            }
        }

        return collect($queues);
    }

    //-------------------------------------------------
    // Helper methods
    //-------------------------------------------------

    private function migrationRun ($data) {
        $tablename = $data["table"]->name();

		//Drop a table
		if ($data["type"] == "droptable") {
            //Insert all of it
            return DB::connection($data["conn"])->statement($data["query"]);
		}
		//Change index/foreign
		else if ($data["type"] == "constraint") {
			//Check if its necessary
			if (!count(DB::select(SyntaxLibrary::checkConstraint($data["column"], $data["table"]->name())))) {
            	return DB::connection($data["conn"])->statement($data["query"]);
			}
		}
		//Table columns
        else if ($data["type"] == "table") {
            //Update table
            if (LOHM::conn($data["conn"])->existsTable($tablename)) {
                $connection     = config("database.connections.".$data["conn"].".database");
                $currenttable   = VirtualTable::fromDatabase($connection, $tablename);
				$changes        = DatabaseHelper::changesNeeded($currenttable, $data["table"]);

                if (count($changes) != 0) {
					for ($i = 0; $i < count($changes); $i++) {
						return DB::connection($data["conn"])->statement($changes[$i]);
					}
				}

				return 0;
            }
            //Create table
            else {
                return DB::connection($data["conn"])->statement($data["query"]);
            }
        }
    }

    private function enqueuer ($queue, $table, $queryType) {
		$query = ["conn" => $this->connection, "table" => $table, "type" => ($queryType === "_queues" ? "table":"constraint")];

        if (is_array($queue)) {
            for ($i = 0; $i < count($queue); $i++) {
				foreach ($queue[$i] as $key => $value) {
					if ($value !== "" && $key !== "column")
						$this->$queryType[] = $query + ["query" => $value, "constraint" => $key, "column" => $queue[$i]["column"]];
				}
            }
        }
        else {
            $this->$queryType[] = $query + ["query" => $queue];
        }
    }
}
