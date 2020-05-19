<?php

namespace Aposoftworks\LOHM\Contracts\DBSyntax;

use Aposoftworks\LOHM\Classes\Virtual\VirtualTable;

interface TableContract {

    /**
     * Returns a syntax for creating a table
     *
	 * @param VirtualTable $table The target table object
	 * @param string[] $columns Columns of the table
	 *
     * @return string table complete syntax
     */
	static function createTable (VirtualTable $table, array $columns = null) : string;

    /**
     * Returns a syntax for altering a table
     *
	 * @param VirtualTable $table The target table object
	 * @param string[] $columns Columns of the table
	 *
     * @return string table complete syntax
     */
	static function alterTable (VirtualTable $table, array $columns = null) : string;

    /**
     * Returns a syntax for checking a table existance
     *
	 * @param string $tablename The target table name
	 *
     * @return string table check complete syntax
     */
	static function checkTable (string $tablename) : string;

    /**
     * Returns a syntax for removing a table existance
     *
	 * @param string $tablename The target table name
	 *
     * @return string table drop complete syntax
     */
	static function dropTable (string $tablename) : string;

    /**
     * Gets all the tables from a database
     *
     * @return string the syntax to get all the tables
     */
	static function getTables () : string;

    /**
     * Gets a specific table creatiom method
     *
	 * @param string $tablename The target table name
	 *
     * @return string the syntax to get a table creation method
     */
	static function showCreateTable ($tablename) : string;
}
