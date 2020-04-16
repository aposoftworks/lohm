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
    protected $method;
    protected $connection;

    protected $_queues        = [];
    protected $_latequeues    = [];

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
        $this->_latequeues[] = ["conn" => $this->connection, "query" => "DROP TABLE IF EXISTS ".$name, "type" => "alter"];
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

        //Generate name
        $classnamewithextension = class_basename($filepath);

        //Actually require
		require $filepath;

		//Get class name
		$classes 	= get_declared_classes();
		$class 		= end($classes);

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
            $queue = $this->_queues[$i];

            if (trim($queue["query"]) != "") {
                $queues[] = function () use ($queue) {
                    return $this->migrationRun($queue);
                };
            }
        }

        for ($i = 0; $i < count($this->_latequeues); $i++) {
            $queue = $this->_latequeues[$i];

            if (trim($queue["query"]) != "") {
                $queues[] = function () use ($queue) {
                    return $this->migrationRun($queue);
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

        if ($data["type"] == "alter") {
            //Reset all indexes and foreign keys
            if (LOHM::conn($data["conn"])->existsTable($tablename)) {
                QueryHelper::dropConstraints($tablename);
                QueryHelper::dropIndexes($tablename);
            }

            //Insert all of it
            DB::connection($data["conn"])->statement($data["query"]);
            return true;
        }
        else {
            //Update table
            if (LOHM::conn($data["conn"])->existsTable($tablename)) {
                $connection     = config("database.connections.".$data["conn"].".database");
                $currenttable   = VirtualTable::fromDatabase($connection, $tablename);
                $changes        = DatabaseHelper::changesNeeded($currenttable, $data["table"]);

                if ($changes === "")
                    return true;
                else
                    DB::connection($data["conn"])->statement($changes);

                return true;
            }
            //Create table
            else {
                DB::connection($data["conn"])->statement($data["query"]);
                return true;
            }
        }
    }

    private function enqueuer ($queue, $table, $queryType = "_queues") {
        $type = $queryType == "_queues" ? "table":"alter";

        if (is_array($queue)) {
            for ($i = 0; $i < count($queue); $i++) {
                $this->$queryType[] = ["conn" => $this->connection, "query" => $queue[$i], "table" => $table, "type" => $type];
            }
        }
        else {
            $this->$queryType[] = ["conn" => $this->connection, "query" => $queue, "table" => $table, "type" => $type];
        }
    }
}
