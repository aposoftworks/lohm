<?php

use Aposoftworks\LOHM\Classes\Facades\LOHM;
use Aposoftworks\LOHM\Classes\Concrete\ConcreteTable as Table;

class {{ $classname }} {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up () {
        LOHM::table('{{ $tablename }}', function (Table $table) {
            $table->id();

            $table->string("title");
            $table->text("content");

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        LOHM::dropTable('{{ $tablename }}');
    }
}
