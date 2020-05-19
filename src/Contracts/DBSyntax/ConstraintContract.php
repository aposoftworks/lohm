<?php

namespace Aposoftworks\LOHM\Contracts\DBSyntax;

use Aposoftworks\LOHM\Classes\Virtual\VirtualColumn;

interface ConstraintContract {

    /**
     * Returns a raw DB string that can be used as a query string
     *
	 * @param VirtualColumn $column You can only pass the virtual column, the tablename and indexname will be used from it
	 * @param string $tablename If ommited, value will be from the virtual column object
	 *
     * @return string a query ready primary key
     */
	static function createPrimary (VirtualColumn $column, string $tablename = null) : string;

    /**
     * Returns a raw DB string that can be used as a query string
     *
	 * @param VirtualColumn $column You can only pass the virtual column, the tablename and indexname will be used from it
	 * @param string $tablename If ommited, value will be from the virtual column object
	 * @param string $indexname If ommited, value will be from the virtual column object
	 *
     * @return string a query ready index
     */
	static function createIndex (VirtualColumn $column, string $tablename = null, string $indexname = null) : string;

    /**
     * Returns a foreign key name
     *
	 * @param VirtualColumn $column Virtual column object
	 *
     * @return string a foreign key name
     */
	static function createForeignName (VirtualColumn $column) : string;

    /**
     * Returns a raw DB string that can be used as a query string
     *
	 * @param VirtualColumn $column You can only pass the virtual column, the tablename and foreignname will be used from it
	 * @param string $tablename If ommited, value will be from the virtual column object
	 * @param string $foreignname If ommited, value will be from the virtual column object
	 *
     * @return string a query ready foreign key
     */
	static function createForeign (VirtualColumn $column, string $tablename = null, string $foreignname = null) : string;

    /**
     * Returns a raw DB string that can be used as a query string
     *
	 * @param VirtualColumn|string $index
	 * @param string $table If you pass a VirtualColumn as the first argument, you can ommit this argument
	 *
     * @return string a query ready to drop a index
     */
	static function dropIndex ($column, string $tablename = null) : string;

    /**
     * Returns a raw DB string that can be used as a query string
     *
	 * @param VirtualColumn|string $index
	 * @param string $table If you pass a VirtualColumn as the first argument, you can ommit this argument
	 *
     * @return string a query ready to drop a index
     */
	static function dropForeign ($column, string $tablename = null) : string;

    /**
     * Returns a raw DB string that can be used as a query string
     *
	 * @param VirtualColumn|string $index
	 * @param string $table If you pass a VirtualColumn as the first argument, you can ommit this argument
	 *
     * @return string a query ready to check a foreign key
     */
	static function checkConstraint ($column, string $tablename = null) : string;
}
