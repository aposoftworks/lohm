<?php

namespace Aposoftworks\LOHM\Traits\MySQL;

//Classes
use Aposoftworks\LOHM\Classes\Virtual\VirtualColumn;

trait ColumnSyntax {

    /**
     * Returns a syntax for handling a column type
     *
	 * @param VirtualColumn $column The column virtual object
	 *
     * @return string the column complete syntax
     */
	public static function type (VirtualColumn $column) : string {
		//Get data
		$attributes = $column->attributes();
		$response 	= $attributes->type;

		//Check for adding length
        if (isset($attributes->length)) {
            $length = $attributes->length;

            //Check for enums
            if (is_array($length)) {
                $length = implode ("', '", preg_replace("/(\'|\")/","", $length));
			}

			if (is_numeric($length))
				$response .= " ($length)";
			else
            	$response .= " ('$length')";
		}

		//Checking for unsigned
		if (isset($attributes->usnigned) && $attributes->unsigned === true)
			$response .= " UNSIGNED";

		//Response
		return $response;
	}

    /**
     * Returns a syntax for handling a column
     *
	 * @param VirtualColumn $column The virtual column
	 *
     * @return string the column complete syntax
     */
	public static function column (VirtualColumn $column) : string {
		//Get data
		$attributes = $column->attributes();
		$name       = $column->name();
		$type		= static::type($column);
		$response 	= "$name $type";

		//Increment
		if (isset($attributes->extra))
			$response .= " $attributes->extra";

		//Nullable
		if (!isset($attributes->nullable) || $attributes->nullable === false)
			$response .= " NOT NULL";
		else
			$response .= " NULL";

		//Default
		if (isset($attributes->default))
			$response .= " DEFAULT '$attributes->default'";

		//Sanitization
		$response = preg_replace("/\s+/", " ", $response);
		$response = trim($response);

		return $response;
	}

    /**
     * Returns a syntax for checking a column existance
     *
	 * @param string $tablename The table name
	 * @param string $columnname The column name
	 *
     * @return string the column check complete syntax
     */
	static function checkColumn (string $tablename, string $columnname) : string {
		return "SHOW COLUMNS FROM $tablename";
	}

    /**
     * Returns all columns from a specific table
     *
	 * @param string $tablename The table name
	 *
     * @return string the column get complete syntax
     */
	static function getColumns (string $tablename) : string {
		return "SHOW COLUMNS FROM $tablename";
	}

    /**
     * Return a specific column from a table
     *
	 * @param string $tablename The table name
	 * @param string $columnname The column name
	 *
     * @return string the column get complete syntax
     */
	static function getColumn (string $tablename, string $columnname) : string {
		return "SHOW COLUMNS FROM $tablename WHERE Field = '$columnname'";
	}
}
