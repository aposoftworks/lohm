<?php

namespace Aposoftworks\LOHM\Classes\Helpers;

//Classes
use Aposoftworks\LOHM\Classes\Facades\LOHM;

//Classes
use Illuminate\Console\Command;
use Aposoftworks\LOHM\Classes\Virtual\VirtualTable;
use Aposoftworks\LOHM\Classes\Virtual\VirtualColumn;
use Aposoftworks\LOHM\Classes\Virtual\VirtualDatabase;

class DatabaseHelper {

    //-------------------------------------------------
    // Diff methods
    //-------------------------------------------------

    public static function diffTable (Command $command, VirtualTable $table, string $prefix = "", $all = true) {
        $fields         = $table->columns();
        $exists         = LOHM::existsTable($table->name());

        $command->line($prefix."<fg=".($exists? "default":"green").">TABLE ".$table->name()."</>");

        //Check new columns
        for ($i = 0; $i < count($fields); $i++) {
            DatabaseHelper::diffColumn($command, $fields[$i], VirtualColumn::fromDatabase($table->database(), $table->name(), $fields[$i]->name()), $prefix."  ", $all);
        }

        //Check removed columns
        $data_fields    = $table->dataColumns();
        $deleted_fields = VirtualTable::fromDatabase($table->database(), $table->name())->columns();

        for ($i = 0; $i < count($deleted_fields); $i++) {
            if (!key_exists($deleted_fields[$i]->name(), $data_fields))
                $command->line($prefix."  <fg=red>COLUMN ".$deleted_fields[$i]->toQuery()."</>");
        }
    }

    public static function diffColumn (Command $command, VirtualColumn $column_new, VirtualColumn $column_current, string $prefix = "", $all = true) {
        if (!$column_current->isValid())
            $command->line($prefix."<fg=green>COLUMN ".$column_new->toQuery()."</>");
        else {
            if (DatabaseHelper::columnEquals($column_new, $column_current)) {
                if ($all) $command->line($prefix."<fg=default>COLUMN ".$column_new->toQuery()."</>");
            }
            else
                $command->line($prefix."<fg=green>COLUMN ".$column_new->toQuery()."</>");
        }
    }

    //-------------------------------------------------
    // Print methods
    //-------------------------------------------------

    public static function printDatabase (Command $command, VirtualDatabase $database) {
        $tables = $database->tables();

        $command->line("<fg=cyan>DATABASE</> ".$database->name());

        for ($i = 0; $i < count($tables); $i++) {
            DatabaseHelper::printTable($command, $tables[$i], "  ");
        }
    }

    public static function printTable (Command $command, VirtualTable $table, string $prefix = "") {
        $fields = $table->columns();

        $command->line($prefix."<fg=magenta>TABLE</> ".$table->name());

        for ($i = 0; $i < count($fields); $i++) {
            DatabaseHelper::printColumn($command, $fields[$i], $prefix."  ");
        }
    }

    public static function printColumn (Command $command, VirtualColumn $column, string $prefix = "") {
        $command->line($prefix."<fg=yellow>COLUMN</> ".$column->toQuery());
    }

    //-------------------------------------------------
    // Comparison methods
    //-------------------------------------------------

    public static function columnEquals (VirtualColumn $obj1, VirtualColumn $obj2) {
        return $obj1->toQuery() === $obj2->toQuery();
    }

    public static function changesNeeded (VirtualTable $current, VirtualTable $needed) {
        $columns_current_data   = $current->dataColumns();
        $columns_needed_data    = $needed->dataColumns();
        $columns_needed         = $needed->columns();
        $queries                = [];

        foreach ($columns_needed_data as $name => $column) {
            //Column already exists
            if (key_exists($name, $columns_current_data)) {
                //Column needs type change
                if (
                    $column["column"]->toQuery() != $columns_current_data[$name]["column"]->toQuery() ||
                    $column["order"] !== $columns_current_data[$name]["order"]
                ) {
                    //Add to the start of the table
                    if ($column["order"] == 0) $queries[] = " MODIFY ".$column["column"]->toQuery()." FIRST ";
                    //Add after a column
                    else $queries[] = " MODIFY ".$column["column"]->toQuery()." AFTER ".$columns_needed[$column["order"] - 1]->name()." ";
                }
            }
            //Add column to table
            else {
                //Add to the start of the table
                if ($column["order"] == 0) $queries[] = " ADD ".$column["column"]->toQuery()." FIRST ";
                //Add after a column
                else $queries[] = " ADD ".$column["column"]->toQuery()." AFTER ".$columns_needed[$column["order"] - 1]->name()." ";
            }
        }

        //Remove unnecessary fields
        foreach ($columns_current_data as $name => $column) {
            //Not required
            if (!key_exists($name, $columns_needed_data)) {
                $queries[] = " DROP COLUMN ".$name." ";
            }
        }

        //Only run if there are actual changes
        if (count($queries) > 0)
            return "ALTER TABLE ".$current->name()." ".implode(", ", $queries);
        else
            return "";
    }
}
