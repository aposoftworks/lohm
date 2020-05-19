<?php

namespace Aposoftworks\LOHM\Traits;

//General
use Exception;
use Aposoftworks\LOHM\Classes\SyntaxLibrary;

//Facades
use Illuminate\Support\Facades\DB;

trait ConstraintSanitizer {
    public static function sanitizeConstraint ($constraint, $tablename) {
		$_preattributes = [];

		//Primary key
		if ($constraint->Key_name == "PRIMARY")
			$_preattributes["key"] = "PRI";
		//Unique
		else if ($constraint->Key_name == $constraint->Column_name)
			$_preattributes["key"] = "UNI";
		//Foreign key
		else {
			$name 				= $constraint->Key_name;

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

		return $_preattributes;
    }
}
