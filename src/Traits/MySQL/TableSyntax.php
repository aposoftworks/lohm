<?php

namespace Aposoftworks\LOHM\Traits\MySQL;

//Classes
use Aposoftworks\LOHM\Classes\Virtual\VirtualTable;

trait TableSyntax {

	/**
	* Returns a syntax for creating a table
	*
	* @param VirtualTable $table The target table object
	* @param string[] $columns Columns of the table
	*
	* @return string table complete syntax
	*/

   public static function createTable (VirtualTable $table, array $columns = null) : string {
	   $rawcolumns	= $table->columns();

	   //Only add if necessary
	   if (is_null($columns)) {
		   $columns	= [];

		   //Stringify all columns
		   for ($i = 0; $i < count($rawcolumns); $i++) {
			   $columns[] = static::column($rawcolumns[$i]);
		   }
	   }

	   //Data
	   $tablename 	= $table->name();
	   $columns	= implode(", ", $columns);

	   //Print
	   return "CREATE TABLE $tablename ( $columns );";
   }

   /**
	* Returns a syntax for altering a table
	*
	* @param VirtualTable $table The target table object
	* @param string[] $columns Columns of the table
	*
	* @return string table complete syntax
	*/
   public static function alterTable (VirtualTable $table, array $columns = null) : string {
	   $rawcolumns	= $table->columns();

	   //Only add if necessary
	   if (is_null($columns)) {
		   $columns	= [];

		   //Stringify all columns
		   for ($i = 0; $i < count($rawcolumns); $i++) {
			   $columns[] = static::column($rawcolumns[$i]);
		   }
	   }

	   //Data
	   $tablename 	= $table->name();
	   $columns	= implode(", ", $columns);

	   //Print
	   return "ALTER TABLE $tablename ( $columns );";
   }

   /**
	* Returns a syntax for checking a table existance
	*
	* @param string $tablename The target table name
	*
	* @return string table check complete syntax
	*/
   public static function checkTable (string $tablename) : string {
	   return "SELECT table_schema FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = '$tablename';";
   }

   /**
	* Returns a syntax for removing a table existance
	*
	* @param string $tablename The target table name
	*
	* @return string table drop complete syntax
	*/
   static function dropTable (string $tablename) : string {
	   return "DROP TABLE $tablename";
   }

   /**
	* Gets all the tables from a database
	*
	* @return string the syntax to get all the tables
	*/
   static function getTables () : string {
	   return "SHOW TABLES";
   }

   /**
	* Gets a specific table creatiom method
	*
	* @param string $tablename The target table name
	*
	* @return string the syntax to get a table creation method
	*/
   static function showCreateTable ($tablename) : string {
	   return "SHOW CREATE TABLE $tablename";
   }
}
