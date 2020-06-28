<?php

namespace Aposoftworks\LOHM\Classes\Helpers;

//Facades
use Illuminate\Support\Facades\DB;
use Aposoftworks\LOHM\Classes\Facades\LOHM;

//Classes
use Illuminate\Console\Command;
use Aposoftworks\LOHM\Classes\SyntaxLibrary;
use Aposoftworks\LOHM\Classes\Virtual\VirtualTable;
use Aposoftworks\LOHM\Classes\Virtual\VirtualColumn;
use Aposoftworks\LOHM\Classes\Virtual\VirtualDatabase;

class DatabaseHelper {

    //-------------------------------------------------
    // Build methods
	//-------------------------------------------------

	public static function getTableCreation ($tablename) {
		$result = DB::select(SyntaxLibrary::showCreateTable($tablename));

		//No table found
		if (count($result) == 0) {
			return "";
		}

		//Return result
		return ((array)$result[0])["Create Table"];
	}

	public static function buildTable ($string) : VirtualTable {
		//Variable declarations
		$statements 	= [];
		$string_columns = [];
		$columns 		= [];

		//Matches
		preg_match_all("/\(.+\)/s", $string, $statements);
		preg_match_all("/.+\n/", $statements[0][0], $string_columns);

		//Remove empty first line
		array_shift($string_columns[0]);

		//Trim
		$string_columns = array_map(function ($column) { return trim($column); }, $string_columns[0]);

		//Get table name
		preg_match("/\`.*\`/", $string, $tablename);
		$tablename = preg_replace("/\`/", "", $tablename[0]);

		//Loop all columns
		for ($i = 0; $i < count($string_columns); $i++) {
			//Check if it is a column or a modifier
			if (preg_match("/^\s*\`.+\`/", $string_columns[$i])) {
				//Get column name
				preg_match("/\`.*\`/", $string_columns[$i], $name_column);
				$name_column = preg_replace("/\`/", "", $name_column[0]);
				$constraints = [];

				//Get constraints
				for ($x = 0; $x < count($string_columns); $x++) {
					//Primary key
					if (
						$x !== $i &&
						(
							preg_match("/PRIMARY KEY.*$name_column/", $string_columns[$x]) ||
							preg_match("/UNIQUE KEY.*$name_column/", $string_columns[$x]) ||
							preg_match("/FOREIGN KEY \(`$name_column`\)/", $string_columns[$x])
						)
					) {
						$constraints[] = $string_columns[$x];
					}
				}

				//Build it
				$columns[] = static::buildColumn ($string_columns[$i], $tablename, $constraints);
			}
		}

		//Build actual virtual table
		return new VirtualTable($tablename, $columns);
	}

	public static function buildColumn ($string, $tablename = "", $constraints = []) {
		//Sets
		$name_column 	= [];
		$attributes 	= [];
		$type 			= [];

		//Get column name
		preg_match("/\`.*\`/", $string, $name_column);
		$name_column = preg_replace("/\`/", "", $name_column[0]);

		//Build type and length (if any)
		preg_match("/$name_column\`\s+.+\s+/U", $string, $type);
		$type = preg_replace("/(.+\`|\s+)/", "", $type[0]);

		//Contains length
		if (preg_match("/(\(|\))/", $type)) {
			$parts 					= explode("(", $type);
			$attributes["type"] 	= $parts[0];

			//Build better length
			$length	= preg_replace("/\)/", "", $parts[1]);

			//Is array
			if (preg_match("/,/", $length)) {
				$length = explode(",", $length);
				$length = array_map(function ($value) { return preg_replace("/(\'|\"|\`)/", "", $value); }, $length);
			}

			$attributes["length"] = $length;
		}
		else {
			$attributes["type"] = $type;
		}

		//Build nullable
		$attributes["nullable"] = !preg_match("/NOT NULL/", $string);

		//Build unsigned
		$attributes["unsigned"] = !!preg_match("/UNSIGNED/", $string);

		//Build default
		if (preg_match("/DEFAULT/", $string)) {
			$value = preg_replace("/(.+DEFAULT\s+|,)/", "", $string);

			if (!preg_match("/NULL/", $value))
				$attributes["default"] = preg_replace("/(\'|\"|\`)/", "", $value);
		}

		//Build constraints
		for ($x = 0; $x < count($constraints); $x++) {
			//Primary key
			if (preg_match("/PRIMARY KEY.*$name_column/", $constraints[$x])) {
				$attributes["key"] = "PRI";
			}
			//INDEX
			else if (preg_match("/UNIQUE KEY.*$name_column/", $constraints[$x])) {
				//Set up
				$name_unique = [];

				//Match
				preg_match_all("/\`.+\`.*\(/", $constraints[$x], $name_unique);
				$name_unique = preg_replace("/\`/", "", $name_unique[0]);
				$name_unique = preg_replace("/\s+.*$/", "", $name_unique[0]);

				$attributes["key"] 	= $name_unique;
			}
			//Foreign key
			else {
				$foreign 				= [];
				$foreign["connection"]	= config("database.default");

				//Get foreign name
				preg_match("/\s+\`.+\`\s+FOREIGN/", $constraints[$x], $name_foreign);
				$name_foreign 		= preg_replace("/(\`|\s|FOREIGN)/", "", $name_foreign[0]);
				$foreign["name"] 	= $name_foreign;

				//References table
				preg_match("/REFERENCES\s+\`.+\`\s/", $constraints[$x], $table_foreign);
				$table_foreign 		= preg_replace("/(\`|\s|REFERENCES)/", "", $table_foreign[0]);
				$foreign["table"]	= $table_foreign;

				//References column
				preg_match("/$table_foreign\`\s+\(.+\)/", $constraints[$x], $column_foreign);
				$column_foreign = preg_replace("/(\`|\s|$table_foreign|\(|\))/", "", $column_foreign[0]);
				$foreign["id"]	= $column_foreign;

				//Cascade update rule
				if (preg_match("/ON UPDATE\s\S+/", $constraints[$x], $update_foreign)) {
					$update_foreign 		= preg_replace("/(\`|\s|ON UPDATE|\(|\))/", "", $update_foreign[0]);

					if ($update_foreign !== "RESTRICT")
					$foreign["UPDATE_RULE"] = $update_foreign;
				}

				//Cascade delete rule
				if (preg_match("/ON DELETE\s\S+/", $constraints[$x], $delete_foreign)) {
					$delete_foreign 		= preg_replace("/(\`|\s|ON DELETE|\(|\))/", "", $delete_foreign[0]);

					if ($delete_foreign !== "RESTRICT")
						$foreign["DELETE_RULE"] = $delete_foreign;
				}

				$attributes["foreign"] = $foreign;
			}
		}

		//Return
		return new VirtualColumn($name_column, $attributes, "", $tablename);
	}

    //-------------------------------------------------
    // Diff methods
    //-------------------------------------------------

    public static function diffTable (Command $command, VirtualTable $table, string $prefix = "", $all = true) {
        $fields         = $table->columns();
		$exists         = LOHM::existsTable($table->name());

        $command->line($prefix."<fg=".($exists? "default":"green").">TABLE ".$table->name()."</>");

		if ($exists) {
			//Check removed columns
			$data_fields    = $table->dataColumns();
			$deleted_fields = VirtualTable::fromDatabase($table->database(), $table->name())->columns();
			$assoc_fields 	= [];

			//To association
			for ($i = 0; $i < count($deleted_fields); $i++) {
				$assoc_fields[$deleted_fields[$i]->name()] = $deleted_fields[$i];
			}

			//Check new columns
			for ($i = 0; $i < count($fields); $i++) {
				if (isset($assoc_fields[$fields[$i]->name()]))
					static::diffColumn($command, $fields[$i], $assoc_fields[$fields[$i]->name()], $prefix."  ", $all);
				else
					$command->line($prefix."<fg=green>COLUMN ".$fields[$i]->toQuery()."</>");
			}

			for ($i = 0; $i < count($deleted_fields); $i++) {
				if (!key_exists($deleted_fields[$i]->name(), $data_fields))
				$command->line($prefix."  <fg=red>COLUMN ".$deleted_fields[$i]->toQuery()."</>");
			}

			$command->line("");
		}
		else {
			//Check new columns
			for ($i = 0; $i < count($fields); $i++) {
				$command->line($prefix."<fg=green>COLUMN ".$fields[$i]->toQuery()."</>");
			}
		}
    }

    public static function diffColumn (Command $command, VirtualColumn $column_new, VirtualColumn $column_current, string $prefix = "", $all = true) {
        if (!$column_current->isValid())
            $command->line($prefix."<fg=green>COLUMN ".$column_new->toQuery()."</>");
        else {
            if (static::columnEquals($column_new, $column_current)) {
                if ($all) $command->line($prefix."<fg=default>COLUMN ".$column_new->toQuery()."</>");
            }
            else
                $command->line($prefix."<fg=green>COLUMN ".$column_new->toQuery()."</>");
        }
    }

    //-------------------------------------------------
    // Print methods
    //-------------------------------------------------

    public static function printDatabase (Command $command, VirtualDatabase $database) {
        $tables = $database->tables();

        $command->line("<fg=cyan>DATABASE</> ".$database->name());

        for ($i = 0; $i < count($tables); $i++) {
            static::printTable($command, $tables[$i], "  ");
        }
    }

    public static function printTable (Command $command, VirtualTable $table, string $prefix = "") {
        $fields = $table->columns();

        $command->line($prefix."<fg=magenta>TABLE</> ".$table->name());

        for ($i = 0; $i < count($fields); $i++) {
            static::printColumn($command, $fields[$i], $prefix."  ");
        }
    }

    public static function printColumn (Command $command, VirtualColumn $column, string $prefix = "") {
        $command->line($prefix."<fg=yellow>COLUMN</> ".$column->toQuery());
    }

    //-------------------------------------------------
    // Comparison methods
    //-------------------------------------------------

    public static function columnEquals (VirtualColumn $obj1, VirtualColumn $obj2) {
		//Column essential diff
		if (strtoupper($obj1->toQuery()) !== strtoupper($obj2->toQuery()))
			return false;

		//Get index data
		$obj1late = $obj1->toLateQuery();
		$obj2late = $obj2->toLateQuery();

		//Indexing diff
		if (count($obj1late) !== count($obj2late))
			return false;

		//No indexes
		if (count($obj1late)) return true;

		//Loop all indexes
		for ($i = 0; $i < count($obj1late); $i++) {
			if (strtoupper($obj1late[$i]) !== strtoupper($obj2late[$i]))
				return false;
		}

		//Object match
        return true;
    }

    public static function changesNeeded (VirtualTable $current, VirtualTable $needed) {
        $columns_current_data   = $current->dataColumns();
        $columns_needed_data    = $needed->dataColumns();
        $columns_needed         = $needed->columns();
        $queries                = [];

        foreach ($columns_needed_data as $name => $column) {
            //Column already exists
            if (key_exists($name, $columns_current_data)) {
                //Column needs type change
                if (!static::columnEquals($column["column"], $columns_current_data[$name]["column"])) {
                    //Add to the start of the table
                    if ($column["order"] == 0) $queries[] = " MODIFY ".$column["column"]->toQuery();
                    //Add after a column
                    else $queries[] = " MODIFY ".$column["column"]->toQuery();
				}
				if ($column["order"] !== $columns_current_data[$name]["order"]) {
                    //Add to the start of the table
                    if ($column["order"] == 0) $queries[] = " MODIFY ".$column["column"]->name()." FIRST ";
                    //Add after a column
                    else $queries[] = " MODIFY ".$column["column"]->name()." AFTER ".$columns_needed[$column["order"] - 1]->name()." ";
				}
            }
            //Add column to table
            else {
                //Add to the start of the table
                if ($column["order"] == 0) $queries[] = " ADD ".$column["column"]->toQuery()." FIRST ";
                //Add after a column
                else $queries[] = " ADD ".$column["column"]->toQuery()." AFTER ".$columns_needed[$column["order"] - 1]->name()." ";
            }
        }

        //Remove unnecessary fields
        foreach ($columns_current_data as $name => $column) {
            //Not required
            if (!key_exists($name, $columns_needed_data)) {
                $queries[] = " DROP COLUMN ".$name." ";
            }
        }

        //Only run if there are actual changes
		if (count($queries) > 0)
			return array_map(function ($query) use ($current) { return "ALTER TABLE ".$current->name()." ".$query; }, $queries);
        else
            return [];
    }
}
