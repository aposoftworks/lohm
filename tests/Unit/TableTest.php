<?php

//General
use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

//Classes
use Aposoftworks\LOHM\Classes\Facades\LOHM;

//Concrete
use Aposoftworks\LOHM\Classes\Concrete\ConcreteTable;

class TableTest extends TestCase {

    use RefreshDatabase;

    /** @test */
    public function createTable () {
		//Start table
        $table = new ConcreteTable("test_table");

		//Base field
		$table->id();

        //Create
        DB::statement($table->toQuery());

		//Assert
	    $this->assertTrue(LOHM::existsTable("test_table"));
    }
}
