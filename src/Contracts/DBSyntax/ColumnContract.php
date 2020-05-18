<?php

namespace Aposoftworks\LOHM\Contracts\DBSyntax;

use Aposoftworks\LOHM\Classes\Virtual\VirtualColumn;

interface ColumnContract {

    /**
     * Returns a syntax for handling a column
     *
	 * @param VirtualColumn $column The column virtual object
	 *
     * @return string the column complete syntax
     */
	static function column (VirtualColumn $column) : string;

    /**
     * Returns a syntax for handling a column type
     *
	 * @param VirtualColumn $column The column virtual object
	 *
     * @return string the column complete syntax
     */
	static function type (VirtualColumn $column) : string;

    /**
     * Returns a syntax for checking a column existance
     *
	 * @param string $tablename The table name
	 * @param string $columnname The column name
	 *
     * @return string the column check complete syntax
     */
	static function checkColumn (string $tablename, string $columnname) : string;

    /**
     * Returns all columns from a specific table
     *
	 * @param string $tablename The table name
	 *
     * @return string the column get complete syntax
     */
	static function getColumns (string $tablename) : string;

    /**
     * Return a specific column from a table
     *
	 * @param string $tablename The table name
	 * @param string $columnname The column name
	 *
     * @return string the column get complete syntax
     */
	static function getColumn (string $tablename, string $columnname) : string;
}
