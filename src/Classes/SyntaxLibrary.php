<?php

namespace Aposoftworks\LOHM\Classes;

use Aposoftworks\LOHM\Contracts\DBSyntax\TableContract;
use Aposoftworks\LOHM\Contracts\DBSyntax\ColumnContract;
use Aposoftworks\LOHM\Contracts\DBSyntax\ConstraintContract;
use Exception;

class SyntaxLibrary {

    //-------------------------------------------------
    // Properties
    //-------------------------------------------------

	private static $conn 		= null;
	private static $type 		= null;
	private static $singleton 	= null;
	private static $class 		= null;

    //-------------------------------------------------
    // Main methods
	//-------------------------------------------------

    /**
     * Set's the syntax library for the chosen method
     *
	 * @param string $connectionname The laravel database connection to be used
	 *
     * @return bool Will return true if the connection was found
     */

	private function _setConnection (string $connectionname) : bool {
		$connection = config("database.connections.$connectionname");

		if (is_null($connection)) {
			return false;
		}
		else {
			static::$conn = $connectionname;
			static::$type = $connection["driver"];

			//Check the database type class
			{
				//Get a instance of the class
				$class	= config("lohm.dictionaries.".$connection["driver"]);
				$object = new $class;

				//Check if it inherits the necessary contracts
				if (!($object instanceof ColumnContract && $object instanceof ConstraintContract && $object instanceof TableContract)) {
					throw new Exception("$class is not a instance of the required interfaces");
				}

				//Finally set it
				static::$class = $class;
			}

			return true;
		}
	}

    //-------------------------------------------------
    // Helper methods
	//-------------------------------------------------

    /**
     * Make sure all the private static fields are initialized
     */

	private static function initialize () : void {
		if (is_null(static::$conn) || is_null(static::$type) || is_null(static::$class)) {
			//Create the singleton instance
			static::$singleton = new static;
			//Ready the singleton
			static::$singleton->_setConnection(config("database.default"));
		}
	}

    /**
     * This will intercept all inbound requests to the library and
	 * make sure to pass them to the according location
     *
     */

	public static function __callStatic ($name, $arguments) {
		//Make sure every call is initialized
		static::initialize();

		//Check if the user is trying to call this class method or the database specific library
		if (method_exists(static::$singleton, "_$name")) {
			return static::$singleton->{"_$name"}(...$arguments);
		}

		//Call a method from the library
		return static::$class::{$name}(...$arguments);
	}
}
