<?php

//General
use Orchestra\Testbench\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

//Classes
use Aposoftworks\LOHM\Classes\Facades\LOHM;

//Concrete
use Aposoftworks\LOHM\Classes\Concrete\ConcreteTable;

class VarcharTest extends TestCase {

    use RefreshDatabase;

    /** @test */
    public function createVarchar () {
		//Start table
        $table = new ConcreteTable("test_table_varchar");

		//Base field
		$table->string("test");

        //Create
        DB::statement($table->toQuery());

		//Assert
	    $this->assertTrue(LOHM::existsTable("test_table_varchar"));
	}

    /** @test */
    public function createCustomSizedVarchar () {
		//Start table
        $table = new ConcreteTable("test_table_varchar_custom");

		//Base field
		$table->string("test", 10);

        //Create
        DB::statement($table->toQuery());

		//Assert
	    $this->assertTrue(LOHM::existsTable("test_table_varchar_custom"));
	}

    /** @test */
    public function createNullableVarchar () {
		//Start table
        $table = new ConcreteTable("test_table_varchar_nullable");

		//Base field
		$table->string("test")->nullable();

        //Create
        DB::statement($table->toQuery());

		//Assert
	    $this->assertTrue(LOHM::existsTable("test_table_varchar_nullable"));
	}
}
