<?php

namespace Aposoftworks\LOHM\Classes\Syntax;

//Traits
use Aposoftworks\LOHM\Traits\MySQL\ColumnSyntax;
use Aposoftworks\LOHM\Traits\MySQL\TableSyntax;
use Aposoftworks\LOHM\Traits\MySQL\ConstraintSyntax;

//Interfaces
use Aposoftworks\LOHM\Contracts\DBSyntax\TableContract;
use Aposoftworks\LOHM\Contracts\DBSyntax\ColumnContract;
use Aposoftworks\LOHM\Contracts\DBSyntax\ConstraintContract;

class MySqlSyntax implements TableContract, ConstraintContract, ColumnContract {

	use TableSyntax;
	use ColumnSyntax;
    use ConstraintSyntax;
}
