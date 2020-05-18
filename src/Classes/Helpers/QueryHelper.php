<?php

namespace Aposoftworks\LOHM\Classes\Helpers;

use Illuminate\Support\Facades\DB;

class QueryHelper {
    public static function checkTable ($tablename) {
		$type = config("database.connections.".config("database.default").".driver");

		switch ($type) {
			case "sqlite":
				return "SELECT name FROM sqlite_master WHERE type='table' AND name= '".$tablename."';";
			default:
			case "mysql":
				return "SELECT table_schema FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '".$tablename."';";
		}
    }

    public static function checkConstraint ($constraintname, $tablename) {
        return "SELECT *
                FROM sys.foreign_keys
                WHERE object_id = OBJECT_ID(N\'dbo.".$constraintname."')
                AND parent_object_id = OBJECT_ID(N'dbo.".$tablename."')";
    }

    public static function dropConstraints ($table) {
        $database       = config("database.connections.".config("database.default").".database");
        $constraints    = DB::select("SELECT * FROM information_schema.INNODB_SYS_FOREIGN WHERE FOR_NAME = '".$database."/".$table."'");

        for ($i = 0; $i < count($constraints); $i++) {
            $constraint = preg_replace("/".$database."\//","", $constraints[$i]->ID);

			static::dropConstraint($table, $constraint);
        }
    }

    public static function dropIndexes ($table) {
        $database       = config("database.connections.".config("database.default").".database");
        $tableid        = DB::select("SELECT * FROM information_schema.INNODB_SYS_TABLES WHERE NAME ='".$database."/".$table."'");

        if (count($tableid) > 0) {
            $indexes = DB::select("SELECT * FROM information_schema.INNODB_SYS_INDEXES WHERE TABLE_ID = '".$tableid[0]->TABLE_ID."' AND NAME != 'PRIMARY'");

            for ($i = 0; $i < count($indexes); $i++) {
                $constraint = $indexes[$i]->NAME;

				static::dropIndex($table, $constraint);
            }
        }
    }

    public static function dropConstraint ($table, $constraintname) {
        DB::connection(config("database.default"))->statement("ALTER TABLE ".$table." DROP FOREIGN KEY ".$constraintname);
    }

    public static function dropIndex ($table, $indexname) {
		DB::connection(config("database.default"))->statement("ALTER TABLE ".$table." DROP INDEX ".$indexname);
    }
}
