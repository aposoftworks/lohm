<?php

use Aposoftworks\LOHM\Classes\Facades\LOHM 				as Schema;
use Aposoftworks\LOHM\Classes\Concrete\ConcreteTable 	as Blueprint;

class {{ $classname }} {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up () {
        Schema::table('{{ $tablename }}', function (Blueprint $table) {
			$table->id();

			$table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropTable('{{ $tablename }}');
    }
}
