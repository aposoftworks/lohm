<?php

//General
use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

//Classes
use Aposoftworks\LOHM\Classes\Facades\LOHM;

//Concrete
use Aposoftworks\LOHM\Classes\Concrete\ConcreteTable;

class IntegerTest extends TestCase {

    use RefreshDatabase;

    /** @test */
    public function createInteger () {
		//Start table
        $table = new ConcreteTable("test_table_int");

		//Base field
		$table->integer("test");

        //Create
        DB::statement($table->toQuery());

		//Assert
	    $this->assertTrue(LOHM::existsTable("test_table_int"));
	}

    /** @test */
    public function createCustomSizedInteger () {
		//Start table
        $table = new ConcreteTable("test_table_int_custom");

		//Base field
		$table->integer("test", 10);

        //Create
        DB::statement($table->toQuery());

		//Assert
	    $this->assertTrue(LOHM::existsTable("test_table_int_custom"));
	}

    /** @test */
    public function createNullableInteger () {
		//Start table
        $table = new ConcreteTable("test_table_int_nullable");

		//Base field
		$table->integer("test")->nullable();

        //Create
        DB::statement($table->toQuery());

		//Assert
	    $this->assertTrue(LOHM::existsTable("test_table_int_nullable"));
	}
}
