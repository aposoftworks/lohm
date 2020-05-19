<?php

namespace Aposoftworks\LOHM\Traits;

trait ColumnSanitizer {
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
}
