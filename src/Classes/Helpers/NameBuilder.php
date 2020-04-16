<?php

namespace Aposoftworks\LOHM\Classes\Helpers;

//Helpers
use Carbon\Carbon;
use Illuminate\Support\Str;

class NameBuilder {
    public static function build ($namestring, $classname = false) {
        $raw    = $namestring;
        $format = config("lohm.default_table_namestructure");

        //Helpers
        $timestamp  = Carbon::now()->format("Y_m_d_u");
        $basicname  = $raw;
        $studlyname = Str::studly($raw);
        $camelname  = Str::camel($raw);

        //Exchange timestamp
        if (preg_match("/{timestamp}/", $format)) {
			if ($classname)
			$format = preg_replace("/{timestamp}/", "", $format);
			else
            	$format = preg_replace("/{timestamp}/", $timestamp, $format);
        }
        //Exchange basicname
        if (preg_match("/{name}/", $format)) {
			if ($classname)
				$format = preg_replace("/{name}/", $studlyname, $format);
			else
            	$format = preg_replace("/{name}/", $basicname, $format);
        }
        //Exchange studlyname
        if (preg_match("/{studly}/", $format)) {
            $format = preg_replace("/{studly}/", $studlyname, $format);
        }
        //Exchange camelname
        if (preg_match("/{camel}/", $format)) {
			if ($classname)
				$format = preg_replace("/{camel}/", $studlyname, $format);
			else
            	$format = preg_replace("/{camel}/", $camelname, $format);
		}

		//Sanitize
		$format = preg_replace("/^(_|-)/", "", $format);
		$format = preg_replace("/(_|-)$/", "", $format);

        return $format;
    }

    public static function isMigration ($name) {
        $format         = config("lohm.default_table_namestructure");
        $cleanformat    = preg_replace("/{\w+}/", "\w+", $format);

        return preg_match("/".$cleanformat."/", $name);

    }
}
