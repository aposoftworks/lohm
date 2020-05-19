<?php

namespace Aposoftworks\LOHM\Traits\MySQL;

//Classes
use Exception;
use Aposoftworks\LOHM\Classes\Virtual\VirtualColumn;

trait ConstraintSyntax {

    /**
     * Returns a raw DB string that can be used as a query string
     *
	 * @param VirtualColumn $column You can only pass the virtual column, the tablename and indexname will be used from it
	 * @param string $tablename If ommited, value will be from the virtual column object
	 *
     * @return string a query ready primary key
     */
	static function createPrimary (VirtualColumn $column, string $tablename = null) : string {
		//Exception of missing data
		if (!($column instanceof VirtualColumn) && is_null($tablename)) {
			throw new Exception("Virtual column not given but expecting it to fill missing data");
		}

		//Column string name
		if ($column instanceof VirtualColumn) {
			$columnname = $column->name();
		}
		else {
			$columnname = $column;
		}

		//Update the tablename based on the column
		if (is_null($tablename)) {
			$tablename = $column->table();
		}

		//Get tablename from the column
		if ($tablename) {
			$tablename = $column->table();
		}

		return "ALTER TABLE $tablename ADD PRIMARY KEY ($columnname)";
	}

	/**
     * Returns a raw DB string that can be used as a query string
     *
	 * @param VirtualColumn $column You can only pass the virtual column, the tablename and indexname will be used from it
	 * @param string $tablename If ommited, value will be from the virtual column object
	 * @param string $indexname If ommited, value will be from the virtual column object
	 *
     * @return string a query ready index
     */
	public static function createIndex ($column, string $tablename = null, string $indexname = null) : string {
		//Exception of missing data
		if (!($column instanceof VirtualColumn) && (is_null($tablename) || is_null($indexname))) {
			throw new Exception("Virtual column not given but expecting it to fill missing data");
		}

		//Column string name
		if ($column instanceof VirtualColumn) {
			$columnname = $column->name();
		}
		else {
			$columnname = $column;
		}

		//Update the tablename based on the column
		if (is_null($tablename)) {
			$tablename = $column->table();
		}

		//Update the index name based on the column
		if (is_null($indexname)) {
			$indexname = $columnname;
		}

		//Get tablename from the column
		if ($tablename) {
			$tablename = $column->table();
		}

		return "CREATE UNIQUE INDEX $indexname ON $tablename ($columnname)";
	}
    /**
     * Returns a foreign key name
     *
	 * @param VirtualColumn $column Virtual column object
	 *
     * @return string a foreign key name
     */
	static function createForeignName (VirtualColumn $column) : string {
		//Get data from the column
		$attributes 		= (object)$column->attributes()->foreign;
		$foreignkeycolumn 	= $column->name();
		$targettable 		= $attributes->table;
		$targetcolumn 		= $attributes->id;

		//Name build
		return $foreignkeycolumn."_".$column->table()."_".$targettable."_".$targetcolumn;
	}

    /**
     * Returns a raw DB string that can be used as a query string
     *
	 * @param VirtualColumn $column You can only pass the virtual column, the tablename and foreignname will be used from it
	 * @param string $tablename If ommited, value will be from the virtual column object
	 * @param string $foreignname If ommited, value will be from the virtual column object
	 *
     * @return string a query ready foreign key
     */
	public static function createForeign (VirtualColumn $column, string $tablename = null, string $foreignname = null) : string {
		//Exception for the virtual column not having foreign data
		if (!isset($column->attributes()->foreign)) {
			throw new Exception("Column does not support foreign key");
		}

		//Get data from the column
		$attributes 		= (object)$column->attributes()->foreign;
		$foreignkeycolumn 	= $column->name();
		$targettable 		= $attributes->table;
		$targetcolumn 		= $attributes->id;

		//Fill table name if necessary
		if (is_null($tablename)) {
			$tablename = $column->table();
		}

		//Fill foreign name if necessary
		if (is_null($foreignname)) {
			if (isset($attributes->name) && !is_null($attributes->name))
				$foreignname = $attributes->name;
			else
				$foreignname = static::createForeignName($column);
		}

		//Build
		$command 	= [];
		$command[] 	= "ALTER TABLE $tablename";
		$command[] 	= "ADD CONSTRAINT $foreignname FOREIGN KEY ($foreignkeycolumn)";
		$command[] 	= "REFERENCES $targettable ($targetcolumn)";
		$command[] 	= isset($attributes->UPDATE_RULE) ? "ON UPDATE ".$attributes->UPDATE_RULE:"";
		$command[] 	= isset($attributes->DELETE_RULE) ? "ON DELETE ".$attributes->DELETE_RULE:"";

		//Sanitize
		$command = implode(" ", $command);
		$command = trim($command);

		return $command;
	}

    /**
     * Returns a raw DB string that can be used as a query string
     *
	 * @param VirtualColumn|string $index
	 * @param string $table If you pass a VirtualColumn as the first argument, you can ommit this argument
	 *
     * @return string a query ready to drop a index
     */
	public static function dropIndex ($column, string $tablename = null) : string {
		//You should pass the table name if the column is a string
		if (!($column instanceof VirtualColumn) && is_null($tablename)) {
			throw new Exception("Could not determine the table name");
		}
		if ($column instanceof VirtualColumn) {
			$foreignname = $column->name();

			//Give table name, if not given
			if (is_null($tablename)) {
				$tablename = $column->table();
			}
		}
		//Just pass the string name
		else {
			$foreignname = $column;
		}

		return "ALTER TABLE $tablename DROP INDEX $foreignname";
	}

    /**
     * Returns a raw DB string that can be used as a query string
     *
	 * @param VirtualColumn|string $index
	 * @param string $table If you pass a VirtualColumn as the first argument, you can ommit this argument
	 *
     * @return string a query ready to drop a index
     */
	public static function dropForeign ($column, string $tablename = null) : string {
		//You should pass the table name if the column is a string
		if (!($column instanceof VirtualColumn) && is_null($tablename)) {
			throw new Exception("Could not determine the table name");
		}

		if ($column instanceof VirtualColumn) {
			$foreignname = $column->name();

			//Give table name, if not given
			if (is_null($tablename)) {
				$tablename = $column->table();
			}
		}
		//Just pass the string name
		else {
			$foreignname = $column;
		}

		return "ALTER TABLE $tablename DROP FOREIGN KEY $foreignname";
	}

    /**
     * Returns a raw DB string that can be used as a query string
     *
	 * @param VirtualColumn|string $index
	 * @param string $table If you pass a VirtualColumn as the first argument, you can ommit this argument
	 *
     * @return string a query ready to check a foreign key from name
     */
	public static function checkConstraint ($column, string $tablename = null) : string {
		//You should pass the table name if the column is a string
		if (!($column instanceof VirtualColumn) && is_null($tablename)) {
			throw new Exception("Could not determine the table name");
		}

		if ($column instanceof VirtualColumn) {
			$constraintname = $column->name();

			//Give table name, if not given
			if (is_null($tablename)) {
				$tablename = $column->table();
			}
		}
		//Just pass the string name
		else {
			$constraintname = $column;
		}

		//Build command
		$command 	= [];
		$command[] 	= "SHOW INDEX FROM $tablename";
		$command[] 	= "WHERE Column_name = '$constraintname'";

		return implode(" ", $command);
	}
}
