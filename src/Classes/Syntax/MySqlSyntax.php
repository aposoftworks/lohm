<?php

namespace Aposoftworks\LOHM\Classes\Syntax;

//Traits
use Aposoftworks\LOHM\Classes\Syntax\MySQL\ColumnSyntax;
use Aposoftworks\LOHM\Classes\Syntax\MySQL\TableSyntax;
use Aposoftworks\LOHM\Classes\Syntax\MySQL\ConstraintSyntax;

//Interfaces
use Aposoftworks\LOHM\Contracts\DBSyntax\TableContract;
use Aposoftworks\LOHM\Contracts\DBSyntax\ColumnContract;
use Aposoftworks\LOHM\Contracts\DBSyntax\ConstraintContract;

class MySqlSyntax implements TableContract, ConstraintContract, ColumnContract {

	use TableSyntax;
	use ColumnSyntax;
    use ConstraintSyntax;
}
