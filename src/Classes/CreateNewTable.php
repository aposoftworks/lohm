<?php

namespace Aposoftworks\LOHM\Classes;

//Helpers
use Aposoftworks\LOHM\Classes\Helpers\NameBuilder;
use Aposoftworks\LOHM\Classes\Helpers\StubBuilder;

class CreateNewTable {
    public static function create ($arguments = [], $options = [], $command) {
        $name       = class_basename($arguments["classname"]);
        $tablepath  = preg_replace("/".$name."/", "", $arguments["classname"]);
        $path       = config("lohm.default_table_directory").$tablepath;
		$filename   = NameBuilder::build($name);
		$classname 	= NameBuilder::build($name, true);
		$tablename 	= is_null($arguments["name"]) ? strtolower($name) : $arguments["name"];
        $stub       = CreateNewTable::getStub($options["template"], $command);

        //Create stub
        $stub = StubBuilder::build($stub, [
            "classname" => $classname,
            "tablename" => $tablename,
        ]);

        //Create path
        if (!is_dir($path)) {
            mkdir($path);
        }

        //Place stub inside migration
        if (file_exists($path.$filename.".php")) {
            return false;
        }
        else {
            file_put_contents($path.$filename.".php", $stub);
        }

        //File created
        return true;
    }

    private static function getStub ($name = "default", $command) {
        $name = "lohm.".$name.".php";

        //Try user application resource
        if (is_file(resource_path("/stubs/".$name))) {
            $path = resource_path("/stubs/".$name);
        }
        //Try package resource
        else if (is_file(__DIR__."/../Stubs/".$name)) {
            $path = __DIR__."/../Stubs/".$name;
        }
        //Go for the default
        else {
            $command->warn("Template not found, using default stub");
            $path = __DIR__."/../Stubs/lohm.default.php";
        }

        return file_get_contents($path);
    }
}
